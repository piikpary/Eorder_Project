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
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'pusher' => [
        'instance_id' => env('PUSHER_INSTANCE_ID'),
        'beam_secret' => env('PUSHER_BEAM_SECRET'),
    ],

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'organization_id' => env('OPENAI_ORGANIZATION_ID'),
        'model' => env('OPENAI_MODEL', 'gpt-4o'),
    ],

    'bakong' => [
    'base_url' => env('BAKONG_BASE_URL', 'https://api-bakong.nbc.gov.kh'),
    'account_id' => env('BAKONG_ACCOUNT_ID', env('BAKONG_KHQR_ACCOUNT_ID')),
    'merchant_name' => env('BAKONG_MERCHANT_NAME', env('BAKONG_KHQR_MERCHANT_NAME', 'VANNY MEAS')),
    'merchant_city' => env('BAKONG_MERCHANT_CITY', env('BAKONG_KHQR_CITY', 'PHNOM PENH')),
    'currency' => env('BAKONG_KHQR_CURRENCY', 'USD'),
    'token' => env('BAKONG_API_TOKEN', env('BAKONG_TOKEN')),
    'test_mode' => env('BAKONG_TEST_MODE', false),
    'qr_lifetime_minutes' => env('BAKONG_QR_LIFETIME_MINUTES', 1440),
    'acquiring_bank' => env('BAKONG_ACQUIRING_BANK'),
    'mobile_number' => env('BAKONG_MOBILE_NUMBER'),
],

'telegram_loyalty' => [
    'bot_token' => env('TELEGRAM_LOYALTY_BOT_TOKEN'),
    'bot_username' => env('TELEGRAM_LOYALTY_BOT_USERNAME', 'sob_loyalty_alert_bot'),
    'chat_id' => env('TELEGRAM_LOYALTY_CHAT_ID'),
],
];