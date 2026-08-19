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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'clickpay' => [
        'base_url'   => env('CLICKPAY_BASE_URL'),
        'server_key' => env('CLICKPAY_SERVER_KEY'),
        'profile_id' => env('CLICKPAY_PROFILE_ID'),
    ],

    'tabby' => [
        'secret_key' => env('TABBY_SECRET_KEY'),
    ],

    'tamara' => [
        'secret_key' => env('TAMARA_SECRET_KEY'),
    ],

    'whatsapp' => [
        'token' => env('WHATSAPP_TOKEN'),
    ],

    'fcm' => [
        'credentials' => env('FIREBASE_CREDENTIALS') ?? storage_path('app/firebase/firebase_cred.json'),
        'project_id' => env('FIREBASE_PROJECT_ID') ?? "naqi-tech",
    ],

];
