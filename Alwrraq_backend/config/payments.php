<?php

return [
    'default_gateway' => env('PAYMENT_GATEWAY', 'moyasar'),

    'currency' => env('PAYMENT_CURRENCY', 'SAR'),

    'hyperpay' => [
        'api_key' => env('HYPERPAY_API_KEY'),
        'secret_key' => env('HYPERPAY_SECRET_KEY'),
        'merchant_id' => env('HYPERPAY_MERCHANT_ID'),
    ],

    'moyasar' => [
        'publishable_key' => env('MOYASAR_PUBLISHABLE_KEY', env('MOYASAR_API_KEY')),
        'secret_key' => env('MOYASAR_SECRET_KEY'),
        'webhook_secret' => env('MOYASAR_WEBHOOK_SECRET'),
        'webhook_secret_hash' => env(
            'MOYASAR_WEBHOOK_SECRET_HASH',
            '405d05a15ddf731834f49edda39eade180a5c159d6efc843d3dbc0818f826611'
        ),
        'api_url' => env('MOYASAR_API_URL', 'https://api.moyasar.com/v1'),
        'timeout' => env('MOYASAR_TIMEOUT', 10),
        'merchant_label' => env('MOYASAR_MERCHANT_LABEL', 'Alwrraq'),
        'apple_pay_enabled' => env('MOYASAR_APPLE_PAY_ENABLED', true),
        'apple_pay_country' => env('MOYASAR_APPLE_PAY_COUNTRY', 'SA'),
        'apple_pay_validation_url' => env('MOYASAR_APPLE_PAY_VALIDATION_URL', 'https://api.moyasar.com/v1/applepay/initiate'),
        'stc_pay_enabled' => env('MOYASAR_STC_PAY_ENABLED', true),
        'google_pay_enabled' => env('MOYASAR_GOOGLE_PAY_ENABLED', true),
        'google_pay_merchant_id' => env('GOOGLE_PAY_MERCHANT_ID'),
        'google_pay_country' => env('GOOGLE_PAY_COUNTRY', 'SA'),
        'google_pay_environment' => env('GOOGLE_PAY_ENVIRONMENT', 'TEST'),
    ],
];
