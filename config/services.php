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

    'resend' => [
        'key' => env('RESEND_KEY'),
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

    'twilio' => [
        // Preferred: set your Account SID and Messaging Service SID separately in .env
        'account_sid' => env('TWILIO_ACCOUNT_SID', null),
        'messaging_service_sid' => env('TWILIO_MESSAGING_SID', null),
        // Backwards-compat: some setups used TWILIO_SID previously (could be MG... or AC...)
        'sid' => env('TWILIO_SID', null),
        'token' => env('TWILIO_TOKEN', null),
        'from' => env('TWILIO_FROM', ''),
    ],

];
