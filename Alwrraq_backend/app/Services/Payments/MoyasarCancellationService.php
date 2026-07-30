<?php

namespace App\Services\Payments;

use App\Models\MoyasarPaymentAttempt;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentCancellationAudit;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class MoyasarCancellationService
{
    public function __construct(private readonly MoyasarPaymentService $moyasar) {}

    public function cancel(Order $order, User $actor, string $reason): Order
    {
        $audit = PaymentCancellationAudit::query()->create([
            'request_uuid' => (string) Str::uuid(),
            'order_id' => $order->id,
            'user_id' => $actor->id,
            'action' => 'cancel',
            'outcome' => 'started',
            'reason' => $reason,
        ]);

        try {
            return DB::transaction(function () use ($order, $actor, $reason, $audit): Order {
                $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);

                $this->assertCancellable($lockedOrder);
                [$paymentId, $amountMinor, $isSharedPayment] = $this->paymentContext($lockedOrder);

                $audit->forceFill([
                    'moyasar_payment_id' => $paymentId,
                    'amount_minor' => $amountMinor,
                ])->save();

                $remotePayment = $this->moyasar->fetchPayment($paymentId);
                $remoteStatus = trim((string) data_get($remotePayment, 'status'));
                $refundedBefore = (int) data_get($remotePayment, 'refunded', 0);

                if ($remoteStatus === 'voided') {
                    return $this->finalize(
                        $lockedOrder,
                        $audit,
                        'void',
                        $actor->id,
                        $reason,
                        $amountMinor
                    );
                }

                if ($remoteStatus === 'refunded' && ! $isSharedPayment) {
                    return $this->finalize(
                        $lockedOrder,
                        $audit,
                        'refund',
                        $actor->id,
                        $reason,
                        $amountMinor
                    );
                }

                if ($remoteStatus === 'refunded' && $isSharedPayment) {
                    $remoteAmount = (int) data_get($remotePayment, 'amount', 0);
                    if ($remoteAmount - $refundedBefore < $amountMinor) {
                        throw new RuntimeException('المبلغ المتبقي في عملية ميسر لا يغطي مبلغ هذا الطلب.');
                    }

                    $remoteStatus = 'paid';
                }

                if (! in_array($remoteStatus, ['authorized', 'paid', 'captured'], true)) {
                    throw new RuntimeException('عملية الدفع الحالية لا تقبل الإلغاء أو استعادة المبلغ.');
                }

                if (! $isSharedPayment) {
                    $audit->forceFill(['action' => 'void'])->save();

                    try {
                        $voidedPayment = $this->moyasar->voidPayment($paymentId);
                        if ((string) data_get($voidedPayment, 'status') === 'voided') {
                            return $this->finalize(
                                $lockedOrder,
                                $audit,
                                'void',
                                $actor->id,
                                $reason,
                                $amountMinor
                            );
                        }
                    } catch (Throwable) {
                        // Re-fetch before deciding whether a refund is safe.
                    }

                    $remotePayment = $this->moyasar->fetchPayment($paymentId);
                    $remoteStatus = trim((string) data_get($remotePayment, 'status'));
                    $refundedBefore = (int) data_get($remotePayment, 'refunded', $refundedBefore);

                    if ($remoteStatus === 'voided') {
                        return $this->finalize(
                            $lockedOrder,
                            $audit,
                            'void',
                            $actor->id,
                            $reason,
                            $amountMinor
                        );
                    }

                    if ($remoteStatus === 'refunded') {
                        return $this->finalize(
                            $lockedOrder,
                            $audit,
                            'refund',
                            $actor->id,
                            $reason,
                            $amountMinor
                        );
                    }
                }

                if (! in_array($remoteStatus, ['paid', 'captured'], true)) {
                    throw new RuntimeException('تعذر عكس عملية الدفع، ولم تصبح العملية قابلة للاسترداد.');
                }

                $audit->forceFill(['action' => 'refund'])->save();
                $refundAmount = $isSharedPayment ? $amountMinor : null;

                try {
                    $refundedPayment = $this->moyasar->refundPayment($paymentId, $refundAmount);
                    $refundedTotal = (int) data_get($refundedPayment, 'refunded', 0);
                    if (
                        (string) data_get($refundedPayment, 'status') === 'refunded'
                        || $refundedTotal >= $refundedBefore + $amountMinor
                    ) {
                        return $this->finalize(
                            $lockedOrder,
                            $audit,
                            'refund',
                            $actor->id,
                            $reason,
                            $amountMinor
                        );
                    }
                } catch (Throwable) {
                    // A final fetch detects a successful refund whose response was lost.
                }

                $remotePayment = $this->moyasar->fetchPayment($paymentId);
                $refundedTotal = (int) data_get($remotePayment, 'refunded', 0);
                if (
                    (string) data_get($remotePayment, 'status') === 'refunded'
                    || $refundedTotal >= $refundedBefore + $amountMinor
                ) {
                    return $this->finalize(
                        $lockedOrder,
                        $audit,
                        'refund',
                        $actor->id,
                        $reason,
                        $amountMinor
                    );
                }

                throw new RuntimeException('فشل إلغاء العملية واستعادة المبلغ من ميسر. لم تتغير حالة الطلب.');
            }, 3);
        } catch (Throwable $exception) {
            $audit->forceFill([
                'outcome' => 'failed',
                'error_code' => $exception instanceof RuntimeException
                    ? 'cancellation_rejected'
                    : 'cancellation_failed',
            ])->save();

            if ($exception instanceof RuntimeException) {
                throw $exception;
            }

            throw new RuntimeException(
                'تعذر إتمام الإلغاء من ميسر. لم تتغير حالة الطلب.',
                previous: $exception
            );
        }
    }

    public function confirmWebhook(string $eventType, array $payment, ?string $eventId = null): void
    {
        $paymentId = trim((string) data_get($payment, 'id'));
        if ($paymentId === '' || ! in_array($eventType, ['payment_voided', 'payment_refunded'], true)) {
            return;
        }

        if ($eventId && PaymentCancellationAudit::query()->where('external_event_id', $eventId)->exists()) {
            return;
        }

        DB::transaction(function () use ($eventType, $payment, $paymentId, $eventId): void {
            $method = $eventType === 'payment_voided' ? 'void' : 'refund';
            $pendingAudits = PaymentCancellationAudit::query()
                ->where('moyasar_payment_id', $paymentId)
                ->whereIn('outcome', ['started', 'failed'])
                ->lockForUpdate()
                ->get();

            $orders = $pendingAudits->isNotEmpty()
                ? Order::query()->whereIn('id', $pendingAudits->pluck('order_id')->filter())->lockForUpdate()->get()
                : $this->ordersForPayment($paymentId, true);

            $remoteAmount = (int) data_get($payment, 'amount', 0);
            $remoteRefunded = (int) data_get($payment, 'refunded', 0);
            if (
                $method === 'refund'
                && $pendingAudits->isEmpty()
                && ($remoteAmount <= 0 || $remoteRefunded < $remoteAmount)
            ) {
                PaymentCancellationAudit::query()->create([
                    'request_uuid' => (string) Str::uuid(),
                    'external_event_id' => $eventId,
                    'moyasar_payment_id' => $paymentId,
                    'action' => 'webhook_refund',
                    'outcome' => 'ignored_partial',
                    'remote_status' => (string) data_get($payment, 'status'),
                    'amount_minor' => $remoteRefunded,
                    'error_code' => 'unmatched_partial_refund',
                ]);

                return;
            }

            foreach ($orders as $order) {
                if ($order->status === 'cancelled' && in_array($order->payment_status, ['voided', 'refunded'], true)) {
                    continue;
                }

                $audit = $pendingAudits->firstWhere('order_id', $order->id)
                    ?? PaymentCancellationAudit::query()->create([
                        'request_uuid' => (string) Str::uuid(),
                        'order_id' => $order->id,
                        'moyasar_payment_id' => $paymentId,
                        'action' => 'webhook_'.$method,
                        'outcome' => 'started',
                        'reason' => 'تأكيد تلقائي من ميسر.',
                    ]);

                $amountMinor = (int) ($audit->amount_minor ?: round((float) $order->grand_total * 100));
                $this->finalize(
                    $order,
                    $audit,
                    $method,
                    $audit->user_id,
                    $audit->reason ?: 'تأكيد تلقائي من ميسر.',
                    $amountMinor
                );
            }

            PaymentCancellationAudit::query()->create([
                'request_uuid' => (string) Str::uuid(),
                'external_event_id' => $eventId,
                'moyasar_payment_id' => $paymentId,
                'action' => 'webhook_'.$method,
                'outcome' => 'confirmed',
                'remote_status' => (string) data_get($payment, 'status'),
                'amount_minor' => $method === 'refund' ? $remoteRefunded : $remoteAmount,
            ]);
        }, 3);
    }

    private function assertCancellable(Order $order): void
    {
        if ($order->status === 'cancelled') {
            throw new RuntimeException('هذا الطلب ملغي مسبقًا.');
        }

        if (in_array($order->payment_status, ['voided', 'refunded'], true)) {
            throw new RuntimeException('تمت إعادة مبلغ هذا الطلب مسبقًا.');
        }

        if ($order->payment_status !== 'paid') {
            throw new RuntimeException('لا يمكن إلغاء الطلب قبل اكتمال الدفع.');
        }
    }

    /**
     * @return array{string, int, bool}
     */
    private function paymentContext(Order $order): array
    {
        $attempt = MoyasarPaymentAttempt::query()
            ->whereNotNull('moyasar_payment_id')
            ->whereJsonContains('order_ids', $order->id)
            ->latest()
            ->first();
        $paymentId = trim((string) ($attempt?->moyasar_payment_id ?: $order->payment_reference));

        if ($paymentId === '') {
            throw new RuntimeException('لا يوجد رقم عملية ميسر مرتبط بهذا الطلب.');
        }

        $amountMinor = (int) data_get($attempt?->order_amounts ?? [], (string) $order->id, 0);
        if ($amountMinor <= 0) {
            $amountMinor = (int) round(max(0, (float) $order->grand_total) * 100);
        }

        if ($amountMinor <= 0) {
            throw new RuntimeException('مبلغ الطلب المرتبط بعملية ميسر غير صالح للاسترداد.');
        }

        $linkedOrderCount = $attempt
            ? count($attempt->order_ids ?? [])
            : Order::query()->where('payment_reference', $paymentId)->count();

        return [$paymentId, $amountMinor, $linkedOrderCount > 1];
    }

    private function finalize(
        Order $order,
        PaymentCancellationAudit $audit,
        string $method,
        ?int $actorId,
        string $reason,
        int $amountMinor
    ): Order {
        $now = now();
        $paymentStatus = $method === 'void' ? 'voided' : 'refunded';

        $order->forceFill([
            'status' => 'cancelled',
            'payment_status' => $paymentStatus,
            'refund_method' => $method,
            'refunded_amount' => $method === 'refund' ? round($amountMinor / 100, 2) : 0,
            'cancelled_by' => $actorId,
            'cancelled_at' => $now,
            'cancel_reason' => $reason,
            'refunded_at' => $method === 'refund' ? $now : null,
            'voided_at' => $method === 'void' ? $now : null,
            'customer_notification_seen_at' => null,
        ])->save();

        Payment::query()
            ->where('transaction_id', $audit->moyasar_payment_id.'-'.$order->id)
            ->update(['payment_status' => $paymentStatus]);

        $audit->forceFill([
            'action' => $method,
            'outcome' => 'succeeded',
            'remote_status' => $paymentStatus,
            'amount_minor' => $amountMinor,
            'error_code' => null,
        ])->save();

        return $order->refresh();
    }

    /**
     * @return Collection<int, Order>
     */
    private function ordersForPayment(string $paymentId, bool $lock = false)
    {
        $attempt = MoyasarPaymentAttempt::query()
            ->where('moyasar_payment_id', $paymentId)
            ->first();
        $query = Order::query()->where(function ($query) use ($attempt, $paymentId): void {
            $query->where('payment_reference', $paymentId);
            if ($attempt) {
                $query->orWhereIn('id', $attempt->order_ids ?? []);
            }
        });

        return $lock ? $query->lockForUpdate()->get() : $query->get();
    }
}
