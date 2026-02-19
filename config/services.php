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

    'google' => [
        'maps_api_key' => env('GOOGLE_MAPS_API_KEY'),
    ],

    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
        'connect_webhook_secret' => env('STRIPE_CONNECT_WEBHOOK_SECRET'),
    ],

    'apple_wallet' => [
        'certificate_path' => env('APPLE_WALLET_CERTIFICATE_PATH', 'wallet/pass.p12'),
        'certificate_password' => env('APPLE_WALLET_CERTIFICATE_PASSWORD'),
        'wwdr_certificate_path' => env('APPLE_WALLET_WWDR_PATH', 'wallet/wwdr.pem'),
        'pass_type_identifier' => env('APPLE_WALLET_PASS_TYPE_ID', 'pass.com.acudetox.membership'),
        'team_identifier' => env('APPLE_WALLET_TEAM_ID'),
        'web_service_url' => env('APPLE_WALLET_WEB_SERVICE_URL'),
    ],

    'google_wallet' => [
        'service_account_path' => env('GOOGLE_WALLET_SERVICE_ACCOUNT_PATH', 'wallet/google-service-account.json'),
        'issuer_id' => env('GOOGLE_WALLET_ISSUER_ID'),
    ],

];
