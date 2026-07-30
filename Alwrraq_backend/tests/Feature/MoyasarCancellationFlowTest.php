<?php

namespace Tests\Feature;

use App\Models\MoyasarPaymentAttempt;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentCancellationAudit;
use App\Models\User;
use App\Services\Payments\MoyasarCancellationService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

class MoyasarCancellationFlowTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('payments.moyasar.publishable_key', 'pk_test_cancellation');
        config()->set('payments.moyasar.secret_key', 'sk_test_cancellation');
        config()->set('payments.moyasar.api_url', 'https://api.moyasar.test/v1');

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('role')->default('customer');
            $table->json('admin_permissions')->nullable();
            $table->timestamps();
        });
        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('service_type');
            $table->string('status');
            $table->string('payment_status');
            $table->string('payment_method')->nullable();
            $table->string('payment_reference')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->string('refund_method')->nullable();
            $table->decimal('refunded_amount', 10, 2)->default(0);
            $table->unsignedBigInteger('cancelled_by')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancel_reason')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->timestamp('voided_at')->nullable();
            $table->timestamp('customer_notification_seen_at')->nullable();
            $table->decimal('grand_total', 10, 2)->default(0);
            $table->timestamps();
        });
        Schema::create('moyasar_payment_attempts', function (Blueprint $table): void {
            $table->id();
            $table->uuid('reference')->unique();
            $table->unsignedBigInteger('user_id');
            $table->string('moyasar_payment_id')->nullable()->unique();
            $table->json('order_ids');
            $table->json('order_amounts');
            $table->unsignedBigInteger('amount_minor');
            $table->string('currency', 3);
            $table->string('status');
            $table->string('payment_method')->nullable();
            $table->text('failure_message')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
        Schema::create('payments', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->string('payment_method');
            $table->string('transaction_id')->unique();
            $table->string('payment_status');
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3);
            $table->timestamps();
        });
        Schema::create('payment_cancellation_audits', function (Blueprint $table): void {
            $table->id();
            $table->uuid('request_uuid')->unique();
            $table->string('external_event_id')->nullable()->unique();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('moyasar_payment_id')->nullable();
            $table->string('action');
            $table->string('outcome');
            $table->string('remote_status')->nullable();
            $table->unsignedBigInteger('amount_minor')->nullable();
            $table->text('reason')->nullable();
            $table->string('error_code')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('payment_cancellation_audits');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('moyasar_payment_attempts');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('users');

        parent::tearDown();
    }

    public function test_void_success_cancels_the_order_exactly_once(): void
    {
        [$admin, $order] = $this->paidOrder('payment-void-1');
        Http::fake([
            'https://api.moyasar.test/v1/payments/payment-void-1' => Http::response($this->remotePayment('payment-void-1', 'paid')),
            'https://api.moyasar.test/v1/payments/payment-void-1/void' => Http::response($this->remotePayment('payment-void-1', 'voided')),
        ]);

        $cancelled = app(MoyasarCancellationService::class)->cancel($order, $admin, 'طلب العميل عبر الإدارة');

        $this->assertSame('cancelled', $cancelled->status);
        $this->assertSame('voided', $cancelled->payment_status);
        $this->assertSame('void', $cancelled->refund_method);
        $this->assertNotNull($cancelled->voided_at);
        $this->assertSame('voided', Payment::query()->firstOrFail()->payment_status);
        $this->assertSame('succeeded', PaymentCancellationAudit::query()->firstOrFail()->outcome);

        $this->expectException(RuntimeException::class);
        app(MoyasarCancellationService::class)->cancel($cancelled, $admin, 'ضغط مكرر');
    }

    public function test_failed_void_falls_back_to_a_full_refund(): void
    {
        [$admin, $order] = $this->paidOrder('payment-refund-1');
        $fetches = 0;
        Http::fake(function (Request $request) use (&$fetches) {
            if ($request->method() === 'POST' && str_ends_with($request->url(), '/void')) {
                return Http::response(['type' => 'invalid_request'], 400);
            }
            if ($request->method() === 'POST' && str_ends_with($request->url(), '/refund')) {
                return Http::response($this->remotePayment('payment-refund-1', 'refunded', 100));
            }

            $fetches++;

            return Http::response($this->remotePayment('payment-refund-1', 'paid'));
        });

        $cancelled = app(MoyasarCancellationService::class)->cancel($order, $admin, 'تعذر تنفيذ الخدمة');

        $this->assertGreaterThanOrEqual(2, $fetches);
        $this->assertSame('refunded', $cancelled->payment_status);
        $this->assertSame('refund', $cancelled->refund_method);
        $this->assertSame(1.0, (float) $cancelled->refunded_amount);
        $this->assertNotNull($cancelled->refunded_at);
    }

    public function test_shared_payment_uses_a_partial_refund_without_voiding_other_orders(): void
    {
        [$admin, $order] = $this->paidOrder('payment-shared-1');
        $secondOrder = Order::query()->create([
            'user_id' => $order->user_id,
            'service_type' => 'research',
            'status' => 'processing',
            'payment_status' => 'paid',
            'payment_method' => 'mada',
            'payment_reference' => 'payment-shared-1',
            'paid_at' => now(),
            'grand_total' => 2,
        ]);
        $attempt = MoyasarPaymentAttempt::query()->firstOrFail();
        $attempt->forceFill([
            'order_ids' => [$order->id, $secondOrder->id],
            'order_amounts' => [(string) $order->id => 100, (string) $secondOrder->id => 200],
            'amount_minor' => 300,
        ])->save();

        Http::fake(function (Request $request) {
            if ($request->method() === 'POST') {
                $this->assertStringEndsWith('/refund', $request->url());
                $this->assertSame(100, $request['amount']);

                return Http::response($this->remotePayment('payment-shared-1', 'paid', 100, 300));
            }

            return Http::response($this->remotePayment('payment-shared-1', 'paid', 0, 300));
        });

        $cancelled = app(MoyasarCancellationService::class)->cancel($order, $admin, 'إلغاء خدمة واحدة');

        $this->assertSame('refunded', $cancelled->payment_status);
        $this->assertSame('paid', $secondOrder->fresh()->payment_status);
        Http::assertNotSent(fn (Request $request): bool => str_ends_with($request->url(), '/void'));
    }

    public function test_failed_void_and_refund_keep_the_order_paid(): void
    {
        [$admin, $order] = $this->paidOrder('payment-failed-1');
        Http::fake(function (Request $request) {
            if ($request->method() === 'POST') {
                return Http::response(['type' => 'api_error'], 400);
            }

            return Http::response($this->remotePayment('payment-failed-1', 'paid'));
        });

        try {
            app(MoyasarCancellationService::class)->cancel($order, $admin, 'فشل خارجي');
            $this->fail('Cancellation should have failed.');
        } catch (RuntimeException) {
            $this->assertSame('processing', $order->fresh()->status);
            $this->assertSame('paid', $order->fresh()->payment_status);
            $this->assertSame('failed', PaymentCancellationAudit::query()->firstOrFail()->outcome);
        }
    }

    public function test_refund_webhook_confirms_a_result_after_the_response_was_lost(): void
    {
        [$admin, $order] = $this->paidOrder('payment-webhook-1');
        PaymentCancellationAudit::query()->create([
            'request_uuid' => (string) Str::uuid(),
            'order_id' => $order->id,
            'user_id' => $admin->id,
            'moyasar_payment_id' => 'payment-webhook-1',
            'action' => 'refund',
            'outcome' => 'failed',
            'amount_minor' => 100,
            'reason' => 'انقطع الاتصال بعد الاسترداد',
            'error_code' => 'cancellation_failed',
        ]);
        $payload = $this->remotePayment('payment-webhook-1', 'refunded', 100);

        $service = app(MoyasarCancellationService::class);
        $service->confirmWebhook('payment_refunded', $payload, 'event-refund-1');
        $service->confirmWebhook('payment_refunded', $payload, 'event-refund-1');

        $order->refresh();
        $this->assertSame('cancelled', $order->status);
        $this->assertSame('refunded', $order->payment_status);
        $this->assertSame('refund', $order->refund_method);
        $this->assertSame(2, PaymentCancellationAudit::query()->count());
        $this->assertSame(1, PaymentCancellationAudit::query()
            ->where('external_event_id', 'event-refund-1')
            ->count());
    }

    public function test_customer_and_employee_without_permission_cannot_cancel(): void
    {
        [, $order] = $this->paidOrder('payment-permission-1');
        $customer = User::query()->findOrFail($order->user_id);
        $employee = User::query()->create([
            'name' => 'Employee Without Cancellation Permission',
            'role' => 'admin',
            'admin_permissions' => ['orders_view'],
        ]);

        $this->actingAs($customer)
            ->patch(route('admin.orders.cancel', $order), ['cancel_reason' => 'غير مصرح'])
            ->assertForbidden();
        $this->actingAs($employee)
            ->patch(route('admin.orders.cancel', $order), ['cancel_reason' => 'غير مصرح'])
            ->assertForbidden();

        $this->assertSame('paid', $order->fresh()->payment_status);
        $this->assertSame(0, PaymentCancellationAudit::query()->count());
    }

    /**
     * @return array{User, Order}
     */
    private function paidOrder(string $paymentId): array
    {
        $admin = User::query()->create([
            'name' => 'Cancellation Admin',
            'role' => 'admin',
            'admin_permissions' => ['orders_view', 'orders_cancel'],
        ]);
        $customer = User::query()->create([
            'name' => 'Cancellation Customer',
            'role' => 'customer',
        ]);
        $order = Order::query()->create([
            'user_id' => $customer->id,
            'service_type' => 'research',
            'status' => 'processing',
            'payment_status' => 'paid',
            'payment_method' => 'mada',
            'payment_reference' => $paymentId,
            'paid_at' => now(),
            'grand_total' => 1,
        ]);
        MoyasarPaymentAttempt::query()->create([
            'reference' => (string) Str::uuid(),
            'user_id' => $customer->id,
            'moyasar_payment_id' => $paymentId,
            'order_ids' => [$order->id],
            'order_amounts' => [(string) $order->id => 100],
            'amount_minor' => 100,
            'currency' => 'SAR',
            'status' => 'paid',
            'payment_method' => 'mada',
            'paid_at' => now(),
        ]);
        Payment::query()->create([
            'order_id' => $order->id,
            'payment_method' => 'mada',
            'transaction_id' => $paymentId.'-'.$order->id,
            'payment_status' => 'paid',
            'amount' => 1,
            'currency' => 'SAR',
        ]);

        return [$admin, $order];
    }

    private function remotePayment(
        string $paymentId,
        string $status,
        int $refunded = 0,
        int $amount = 100
    ): array {
        return [
            'id' => $paymentId,
            'status' => $status,
            'amount' => $amount,
            'refunded' => $refunded,
            'currency' => 'SAR',
        ];
    }
}
