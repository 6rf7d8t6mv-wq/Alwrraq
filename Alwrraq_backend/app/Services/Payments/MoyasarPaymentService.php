<?php

namespace App\Services\Payments;

use App\Models\MoyasarPaymentAttempt;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Services\ResumeDocumentService;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class MoyasarPaymentService
{
    public function isConfigured(): bool
    {
        return filled(config('payments.moyasar.publishable_key'))
            && filled(config('payments.moyasar.secret_key'));
    }

    /**
     * @param  Collection<int, Order>  $orders
     */
    public function createAttempt(User $user, Collection $orders): MoyasarPaymentAttempt
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('Moyasar API keys are not configured.');
        }

        if ($orders->isEmpty()) {
            throw new RuntimeException('At least one order is required.');
        }

        $orderAmounts = $orders->mapWithKeys(function (Order $order) use ($user): array {
            if ((int) $order->user_id !== (int) $user->id || $order->payment_status === 'paid') {
                throw new RuntimeException('The payment contains an unavailable order.');
            }

            return [(string) $order->id => $this->toMinorUnits((float) $order->grand_total)];
        })->all();

        $amountMinor = array_sum($orderAmounts);
        if ($amountMinor < 100) {
            throw new RuntimeException('Moyasar requires a minimum payment amount of 1 SAR.');
        }

        return MoyasarPaymentAttempt::query()->create([
            'reference' => (string) Str::uuid(),
            'user_id' => $user->id,
            'order_ids' => array_map('intval', array_keys($orderAmounts)),
            'order_amounts' => $orderAmounts,
            'amount_minor' => $amountMinor,
            'currency' => strtoupper((string) config('payments.currency', 'SAR')),
            'status' => 'pending',
        ]);
    }

    public function rememberCreatedPayment(MoyasarPaymentAttempt $attempt, array $payment): void
    {
        $paymentId = trim((string) data_get($payment, 'id'));
        if ($paymentId === '') {
            return;
        }

        $attempt->forceFill([
            'moyasar_payment_id' => $paymentId,
            'status' => (string) data_get($payment, 'status', 'initiated'),
            'payment_method' => $this->paymentMethod($payment),
        ])->save();
    }

    public function fetchPayment(string $paymentId): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('Moyasar API keys are not configured.');
        }

        return $this->api()
            ->get('/payments/'.rawurlencode($paymentId))
            ->throw()
            ->json();
    }

    public function voidPayment(string $paymentId): array
    {
        return $this->api()
            ->post('/payments/'.rawurlencode($paymentId).'/void')
            ->throw()
            ->json();
    }

    public function refundPayment(string $paymentId, ?int $amountMinor = null): array
    {
        $request = $this->api();

        return $request
            ->post(
                '/payments/'.rawurlencode($paymentId).'/refund',
                $amountMinor === null ? [] : ['amount' => $amountMinor]
            )
            ->throw()
            ->json();
    }

    public function verifyAndComplete(MoyasarPaymentAttempt $attempt, array $remotePayment): bool
    {
        $paymentId = trim((string) data_get($remotePayment, 'id'));
        $status = trim((string) data_get($remotePayment, 'status'));
        $amount = (int) data_get($remotePayment, 'amount', 0);
        $currency = strtoupper(trim((string) data_get($remotePayment, 'currency')));
        $reference = trim((string) data_get($remotePayment, 'metadata.attempt_reference'));

        if (
            $paymentId === ''
            || $reference !== $attempt->reference
            || $amount !== $attempt->amount_minor
            || $currency !== $attempt->currency
        ) {
            $attempt->forceFill([
                'status' => 'failed',
                'failure_message' => 'Payment verification data did not match the local attempt.',
            ])->save();

            return false;
        }

        if ($status !== 'paid') {
            $attempt->forceFill([
                'moyasar_payment_id' => $paymentId,
                'status' => $status !== '' ? $status : 'failed',
                'payment_method' => $this->paymentMethod($remotePayment),
                'failure_message' => (string) data_get($remotePayment, 'source.message'),
            ])->save();

            return false;
        }

        DB::transaction(function () use ($attempt, $remotePayment, $paymentId): void {
            $lockedAttempt = MoyasarPaymentAttempt::query()->lockForUpdate()->findOrFail($attempt->id);

            $method = $this->paymentMethod($remotePayment);
            $orders = Order::query()
                ->whereIn('id', $lockedAttempt->order_ids)
                ->where('user_id', $lockedAttempt->user_id)
                ->lockForUpdate()
                ->get();

            if ($orders->count() !== count($lockedAttempt->order_ids)) {
                throw new RuntimeException('One or more payment orders are unavailable.');
            }

            foreach ($orders as $order) {
                $expectedAmount = (int) ($lockedAttempt->order_amounts[(string) $order->id] ?? 0);
                if ($expectedAmount <= 0) {
                    throw new RuntimeException('The stored order amount is invalid.');
                }

                if ($order->payment_status !== 'paid') {
                    $order->forceFill([
                        'status' => 'processing',
                        'payment_status' => 'paid',
                        'payment_method' => $method,
                        'payment_reference' => $paymentId,
                        'paid_at' => now(),
                    ])->save();
                }

                Payment::query()->firstOrCreate(
                    ['transaction_id' => $paymentId.'-'.$order->id],
                    [
                        'order_id' => $order->id,
                        'payment_method' => $method,
                        'payment_status' => 'paid',
                        'amount' => $expectedAmount / 100,
                        'currency' => $lockedAttempt->currency,
                    ]
                );
            }

            $lockedAttempt->forceFill([
                'moyasar_payment_id' => $paymentId,
                'status' => 'paid',
                'payment_method' => $method,
                'failure_message' => null,
                'paid_at' => now(),
            ])->save();
        });

        Order::query()
            ->whereIn('id', $attempt->order_ids)
            ->where('service_type', 'resume')
            ->with('resumeDraft.order')
            ->get()
            ->each(function (Order $order): void {
                if (! $order->resumeDraft) {
                    return;
                }
                try {
                    app(ResumeDocumentService::class)->ensurePdf($order->resumeDraft);
                } catch (Throwable $exception) {
                    Log::error('Paid resume PDF generation failed.', [
                        'order_id' => $order->id,
                        'resume_draft_id' => $order->resumeDraft->id,
                        'error' => $exception->getMessage(),
                    ]);
                }
            });

        return true;
    }

    public function paymentMethod(array $payment): string
    {
        return match ((string) data_get($payment, 'source.type')) {
            'applepay' => 'apple_pay',
            'googlepay' => 'google_pay',
            'stcpay' => 'stc_pay',
            default => match ((string) data_get($payment, 'source.company')) {
                'mada' => 'mada',
                'master', 'mastercard' => 'mastercard',
                'amex' => 'amex',
                'unionpay' => 'unionpay',
                default => 'visa',
            },
        };
    }

    public function reconcilePendingAttempts(?User $user = null): int
    {
        if (! $this->isConfigured()) {
            return 0;
        }

        $scope = $user ? 'user-'.$user->id : 'all';
        if (! Cache::add('moyasar:reconcile:lock', now()->timestamp, 30)) {
            return 0;
        }

        $baseQuery = MoyasarPaymentAttempt::query()
            ->where('created_at', '>=', now()->subDays(30))
            ->when($user, fn ($query) => $query->where('user_id', $user->id));

        $pendingAttempts = (clone $baseQuery)
            ->where('status', '!=', 'paid')
            ->oldest()
            ->limit(100)
            ->get();

        $incompletePaidAttempts = (clone $baseQuery)
            ->where('status', 'paid')
            ->latest()
            ->limit(100)
            ->get()
            ->filter(fn (MoyasarPaymentAttempt $attempt): bool => $this->attemptNeedsRepair($attempt));

        $attempts = $pendingAttempts
            ->concat($incompletePaidAttempts)
            ->unique('id')
            ->take(100)
            ->keyBy('reference');

        if ($attempts->isEmpty()) {
            return 0;
        }

        $completed = 0;
        $seenReferences = [];

        try {
            $page = 1;
            do {
                $response = $this->api()
                    ->get('/payments', ['page' => $page])
                    ->throw()
                    ->json();

                foreach ((array) data_get($response, 'payments', []) as $remotePayment) {
                    if ((string) data_get($remotePayment, 'status') !== 'paid') {
                        continue;
                    }

                    $reference = trim((string) data_get(
                        $remotePayment,
                        'metadata.attempt_reference'
                    ));
                    $attempt = $attempts->get($reference);
                    if (! $attempt) {
                        continue;
                    }

                    $seenReferences[$reference] = true;
                    if ($this->verifyAndComplete($attempt, $remotePayment)) {
                        $completed++;
                    }
                }

                $totalPages = min(10, (int) data_get($response, 'meta.total_pages', 1));
                $page++;
            } while ($page <= $totalPages && $completed < $attempts->count());
        } catch (Throwable $exception) {
            Log::warning('Moyasar payment list reconciliation failed.', [
                'scope' => $scope,
                'exception' => $exception::class,
            ]);
        }

        foreach ($attempts as $reference => $attempt) {
            if (isset($seenReferences[$reference]) || blank($attempt->moyasar_payment_id)) {
                continue;
            }

            try {
                if ($this->verifyAndComplete(
                    $attempt,
                    $this->fetchPayment($attempt->moyasar_payment_id)
                )) {
                    $completed++;
                }
            } catch (Throwable $exception) {
                Log::warning('Moyasar individual payment reconciliation failed.', [
                    'attempt_id' => $attempt->id,
                    'exception' => $exception::class,
                ]);
            }
        }

        return $completed;
    }

    private function api(): PendingRequest
    {
        return Http::baseUrl(rtrim((string) config('payments.moyasar.api_url'), '/'))
            ->withBasicAuth((string) config('payments.moyasar.secret_key'), '')
            ->acceptJson()
            ->timeout((int) config('payments.moyasar.timeout', 10))
            ->retry(1, 250);
    }

    private function attemptNeedsRepair(MoyasarPaymentAttempt $attempt): bool
    {
        $orderIds = array_values(array_filter(array_map(
            'intval',
            $attempt->order_ids ?? []
        )));

        if ($orderIds === []) {
            return true;
        }

        $orders = Order::query()
            ->whereIn('id', $orderIds)
            ->where('user_id', $attempt->user_id)
            ->get(['id', 'payment_status']);

        if ($orders->count() !== count($orderIds) || blank($attempt->moyasar_payment_id)) {
            return true;
        }

        foreach ($orders as $order) {
            if (
                $order->payment_status !== 'paid'
                || ! Payment::query()
                    ->where('transaction_id', $attempt->moyasar_payment_id.'-'.$order->id)
                    ->exists()
            ) {
                return true;
            }
        }

        return false;
    }

    private function toMinorUnits(float $amount): int
    {
        return (int) round($amount * 100);
    }
}
