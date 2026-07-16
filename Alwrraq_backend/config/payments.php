<?php

return [
    'default_gateway' => env('PAYMENT_GATEWAY', 'mock'),

    'currency' => env('PAYMENT_CURRENCY', 'SAR'),

    'hyperpay' => [
        'api_key' => env('HYPERPAY_API_KEY'),
        'secret_key' => env('HYPERPAY_SECRET_KEY'),
        'merchant_id' => env('HYPERPAY_MERCHANT_ID'),
    ],

    'moyasar' => [
        'api_key' => env('MOYASAR_API_KEY'),
        'secret_key' => env('MOYASAR_SECRET_KEY'),
        'merchant_id' => env('MOYASAR_MERCHANT_ID'),
    ],
];
