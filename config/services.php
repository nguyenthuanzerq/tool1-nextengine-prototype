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

    'next_engine' => [
        'base_uri' => env('NEXT_ENGINE_BASE_URI'),
        'api_uri' => env('NEXT_ENGINE_API_URI'),
        'redirect_uri' => env('NEXT_ENGINE_REDIRECT_URI'),
    ],

    'yahoo' => [
        'redirect_uri' => env('YAHOO_REDIRECT_URI'),
    ],

    'shopify' => [
        'redirect_uri' => env('SHOPIFY_REDIRECT_URI'),
        'scopes' => env('SHOPIFY_SCOPES', 'read_orders,write_inventory,read_all_orders'),
        'api_version' => env('SHOPIFY_API_VERSION', '2024-04'),
    ],

];
