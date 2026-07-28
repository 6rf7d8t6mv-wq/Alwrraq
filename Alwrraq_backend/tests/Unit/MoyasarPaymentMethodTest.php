<?php

namespace Tests\Unit;

use App\Services\Payments\MoyasarPaymentService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class MoyasarPaymentMethodTest extends TestCase
{
    #[DataProvider('paymentMethods')]
    public function test_it_maps_every_supported_moyasar_payment_method(array $source, string $expected): void
    {
        $service = new MoyasarPaymentService();

        $this->assertSame($expected, $service->paymentMethod([
            'source' => $source,
        ]));
    }

    public static function paymentMethods(): array
    {
        return [
            'mada card' => [['type' => 'creditcard', 'company' => 'mada'], 'mada'],
            'visa card' => [['type' => 'creditcard', 'company' => 'visa'], 'visa'],
            'mastercard' => [['type' => 'creditcard', 'company' => 'master'], 'mastercard'],
            'american express' => [['type' => 'creditcard', 'company' => 'amex'], 'amex'],
            'unionpay' => [['type' => 'creditcard', 'company' => 'unionpay'], 'unionpay'],
            'apple pay' => [['type' => 'applepay'], 'apple_pay'],
            'stc pay' => [['type' => 'stcpay'], 'stc_pay'],
            'google pay' => [['type' => 'googlepay'], 'google_pay'],
        ];
    }
}
