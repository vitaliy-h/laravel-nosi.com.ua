<?php

return [
    'providers' => ['google', 'facebook', 'telegram'],

    'google' => [
        'client_id'     => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect'      => env('GOOGLE_REDIRECT_URI', '/auth/social/google/callback'),
    ],

    'facebook' => [
        'client_id'     => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect'      => env('FACEBOOK_REDIRECT_URI', '/auth/social/facebook/callback'),
    ],

    'telegram' => [
        'client_id'     => env('TELEGRAM_BOT_ID'),
        'client_secret' => env('TELEGRAM_BOT_TOKEN'),
        'redirect'      => env('TELEGRAM_REDIRECT_URI', '/auth/social/telegram/callback'),
    ],
];
