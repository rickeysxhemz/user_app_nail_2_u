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
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'github' => [
        'client_id' => config('GOOGLE_CLIENT_ID'),
        'client_secret' => config('GOOGLE_CLIENT_SECRET'),
        'redirect' => config('GOOGLE_REDIRECT_URL'),
    ],

    'facebook' => [
        'client_id' => env('constants.FACEBOOK_APP_CLIENT_ID'),
        'client_secret' => env('constants.FACEBOOK_APP_CLIENT_SECRET'),
        'redirect' => env('constants.FACEBOOK_CALLBACK_URL'),
    ],

    'google' => [
        'client_id' => config('constants.GOOGLE_CLIENT_ID'),
        'client_secret' => config('constants.GOOGLE_CLIENT_SECRET'),
        'redirect' => config('constants.GOOGLE_REDIRECT_URL'),
    ],

];
