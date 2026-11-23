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

    'telr' => [
        'store_id' => env('TELR_STORE_ID', ''),
        'auth_key' => env('TELR_AUTH_KEY', ''),
        'test_mode' => env('TELR_TEST_MODE', '1'), // 1 for test, 0 for live
    ],

    'tabby' => [
        'public_key'    => env('TABBY_PUBLIC_KEY', 'pk_test_01a1b9a8-0a43-a60d-3dfe-7d1a7faae77b'),
        'secret_key'    => env('TABBY_SECRET_KEY', 'sk_test_01a1b9a8-0a43-a60d-3dfe-7d1a8fa5a4d7'),
        'merchant_code' => env('TABBY_MERCHANT_CODE', 'Qas12'),
        'api_url'       => env('TABBY_API_URL', 'https://api.tabby.ai/api/v2'),
    ],

    'tamara' => [
        'api_token'          => env('TAMARA_API_TOKEN', ''),
        'public_key'         => env('TAMARA_PUBLIC_KEY', ''),
        'notification_token' => env('TAMARA_NOTIFICATION_TOKEN', ''),
        'api_url'            => env('TAMARA_API_URL', 'https://api.tamara.co'),
    ],

];
