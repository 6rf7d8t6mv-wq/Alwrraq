<?php

namespace Tests\Unit;

use App\Http\Controllers\MoyasarPaymentController;
use App\Models\MoyasarPaymentAttempt;
use App\Services\Payments\MoyasarPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Mockery;
use Tests\TestCase;

class MoyasarPaymentControllerTest extends TestCase
{
    public function test_completed_wallet_payment_is_verified_and_completed_immediately(): void
    {
        Auth::shouldReceive('id')->once()->andReturn(7);

        $attempt = new MoyasarPaymentAttempt([
            'reference' => 'attempt-reference',
            'user_id' => 7,
        ]);
        $attempt->id = 41;

        $payment = [
            'id' => 'payment-id',
            'status' => 'paid',
            'source' => ['type' => 'applepay', 'company' => 'mada'],
        ];

        $moyasar = Mockery::mock(MoyasarPaymentService::class);
        $moyasar->shouldReceive('rememberCreatedPayment')
            ->once()
            ->with($attempt, $payment);
        $moyasar->shouldReceive('fetchPayment')
            ->once()
            ->with('payment-id')
            ->andReturn($payment);
        $moyasar->shouldReceive('verifyAndComplete')
            ->once()
            ->with($attempt, $payment)
            ->andReturnTrue();

        $response = (new MoyasarPaymentController())->remember(
            Request::create('/payments/moyasar/attempts/attempt-reference', 'POST', $payment),
            $attempt,
            $moyasar
        );

        $this->assertSame(200, $response->status());
        $this->assertSame([
            'saved' => true,
            'paid' => true,
            'redirect_url' => route('orders.index'),
        ], $response->getData(true));
    }

    public function test_webhook_accepts_the_registered_secret_by_its_hash(): void
    {
        config()->set('payments.moyasar.webhook_secret', null);
        config()->set(
            'payments.moyasar.webhook_secret_hash',
            hash('sha256', 'registered-webhook-secret')
        );

        $response = (new MoyasarPaymentController())->webhook(
            Request::create('/payments/moyasar/webhook', 'POST', [
                'secret_token' => 'registered-webhook-secret',
            ]),
            Mockery::mock(MoyasarPaymentService::class)
        );

        $this->assertSame(200, $response->status());
        $this->assertSame(['received' => true], $response->getData(true));
    }

    public function test_webhook_rejects_an_incorrect_secret(): void
    {
        config()->set('payments.moyasar.webhook_secret', null);
        config()->set(
            'payments.moyasar.webhook_secret_hash',
            hash('sha256', 'registered-webhook-secret')
        );

        $response = (new MoyasarPaymentController())->webhook(
            Request::create('/payments/moyasar/webhook', 'POST', [
                'secret_token' => 'incorrect-secret',
            ]),
            Mockery::mock(MoyasarPaymentService::class)
        );

        $this->assertSame(401, $response->status());
    }
}
