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

    'beeline_cloudpbx' => [
        'base_url' => env('BEELINE_CLOUDPBX_BASE_URL', 'https://cloudpbx.beeline.ru'),
        'api_token' => env('BEELINE_CLOUDPBX_API_TOKEN', ''),
        'sync_date_from' => env('BEELINE_SYNC_DATE_FROM', '2026-02-19 00:00:00'),
        'sync_department' => env('BEELINE_SYNC_DEPARTMENT', 'Отдел продаж ИТ'),
    ],

];
