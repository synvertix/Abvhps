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

    'cashfree' => [
        'app_id'                => env('CASHFREE_APP_ID', ''),
        'secret_key'            => env('CASHFREE_SECRET_KEY', ''),
        'verify_client_id'      => env('CASHFREE_VERIFY_CLIENT_ID', ''),
        'verify_client_secret'  => env('CASHFREE_VERIFY_CLIENT_SECRET', ''),
        'verification_base_url' => env('CASHFREE_VERIFICATION_BASE_URL', ''),
        'api_version'           => env('CASHFREE_API_VERSION', '2023-08-01'),
        'environment'           => env('CASHFREE_ENV', 'sandbox'), // 'sandbox' or 'production'
    ],

    'razorpay' => [
        'key_id' => env('RAZORPAY_KEY_ID', ''),
        'key_secret' => env('RAZORPAY_KEY_SECRET', ''),
        'webhook_secret' => env('RAZORPAY_WEBHOOK_SECRET', ''),
        'mode' => env('RAZORPAY_MODE', 'test'), // 'test' or 'live'
    ],

    'fast2sms' => [
        'api_key' => env('FAST2SMS_API_KEY', ''),
        'entity_id' => env('FAST2SMS_ENTITY_ID', ''),
        'sender_id' => env('FAST2SMS_SENDER_ID', 'ABVHPS'),
        'message_id' => env('FAST2SMS_MESSAGE_ID', ''),
        'template_id' => env('FAST2SMS_TEMPLATE_ID', ''),
    ],

];
