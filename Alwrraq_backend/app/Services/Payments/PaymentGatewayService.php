<?php

namespace App\Services\Payments;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Str;

class PaymentGatewayService
{
    public const METHODS = [
        'apple_pay',
        'google_pay',
        'mada',
        'visa',
        'mastercard',
    ];

    public function createPayment(Order $order, string $method): Payment
    {
        $status = $this->simulateGatewayStatus();

        return Payment::query()->create([
            'order_id' => $order->id,
            'payment_method' => $method,
            'transaction_id' => 'PAY-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(8)),
            'payment_status' => $status,
            'amount' => $order->grand_total,
            'currency' => config('payments.currency', 'SAR'),
        ]);
    }

    public function markOrderFromPayment(Order $order, Payment $payment): void
    {
        if ($payment->payment_status !== 'paid') {
            $order->forceFill([
                'payment_status' => $payment->payment_status === 'failed' ? 'unpaid' : $payment->payment_status,
                'payment_method' => $payment->payment_method,
                'payment_reference' => $payment->transaction_id,
            ])->save();

            return;
        }

        $order->forceFill([
            'status' => 'processing',
            'payment_status' => 'paid',
            'payment_method' => $payment->payment_method,
            'payment_reference' => $payment->transaction_id,
            'paid_at' => now(),
        ])->save();
    }

    private function simulateGatewayStatus(): string
    {
        return request()->boolean('simulate_failed_payment') ? 'failed' : 'paid';
    }
}
