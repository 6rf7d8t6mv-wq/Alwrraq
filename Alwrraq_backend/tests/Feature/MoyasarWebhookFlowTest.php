<?php

namespace Tests\Feature;

use App\Models\MoyasarPaymentAttempt;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MoyasarWebhookFlowTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
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
            $table->decimal('print_total', 10, 2)->default(0);
            $table->decimal('binding_total', 10, 2)->default(0);
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
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('moyasar_payment_attempts');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('users');

        parent::tearDown();
    }

    public function test_paid_apple_pay_webhook_completes_the_order_exactly_once(): void
    {
        config()->set('payments.moyasar.webhook_secret', 'webhook-test-secret');
        config()->set('payments.moyasar.webhook_secret_hash', null);

        $user = User::query()->create(['name' => 'Webhook Test Customer']);
        $order = Order::query()->create([
            'user_id' => $user->id,
            'service_type' => 'notes',
            'status' => 'new',
            'payment_status' => 'unpaid',
            'print_total' => 1,
            'binding_total' => 0,
            'grand_total' => 1,
        ]);
        $attempt = MoyasarPaymentAttempt::query()->create([
            'reference' => '8a20e280-a13e-4f5d-bdc9-c33854657830',
            'user_id' => $user->id,
            'order_ids' => [$order->id],
            'order_amounts' => [(string) $order->id => 100],
            'amount_minor' => 100,
            'currency' => 'SAR',
            'status' => 'pending',
        ]);
        $payload = [
            'secret_token' => 'webhook-test-secret',
            'type' => 'payment_paid',
            'data' => [
                'id' => 'payment-apple-pay-1',
                'status' => 'paid',
                'amount' => 100,
                'currency' => 'SAR',
                'metadata' => [
                    'attempt_reference' => $attempt->reference,
                ],
                'source' => [
                    'type' => 'applepay',
                    'company' => 'mada',
                ],
            ],
        ];

        $this->postJson(route('moyasar.webhook'), $payload)->assertOk();
        $this->postJson(route('moyasar.webhook'), $payload)->assertOk();

        $order->refresh();
        $attempt->refresh();

        $this->assertSame('paid', $order->payment_status);
        $this->assertSame('processing', $order->status);
        $this->assertSame('apple_pay', $order->payment_method);
        $this->assertSame('payment-apple-pay-1', $order->payment_reference);
        $this->assertSame('paid', $attempt->status);
        $this->assertSame('apple_pay', $attempt->payment_method);
        $this->assertSame(1, Payment::query()->count());
    }
}
