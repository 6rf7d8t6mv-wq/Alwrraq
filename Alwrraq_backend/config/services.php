<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'google_translation' => [
        'api_key' => env('GOOGLE_TRANSLATE_API_KEY'),
        'endpoint' => env('GOOGLE_TRANSLATE_ENDPOINT', 'https://translation.googleapis.com/language/translate/v2'),
        'timeout' => env('GOOGLE_TRANSLATE_TIMEOUT', 8),
    ],

    'mymemory_translation' => [
        'enabled' => env('MYMEMORY_TRANSLATE_ENABLED', true),
        'endpoint' => env('MYMEMORY_TRANSLATE_ENDPOINT', 'https://api.mymemory.translated.net/get'),
        'timeout' => env('MYMEMORY_TRANSLATE_TIMEOUT', 8),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];
