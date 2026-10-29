<?php

/*
|--------------------------------------------------------------------------
| Site system messages
|--------------------------------------------------------------------------
|
| Fixed texts of the site that are not edited in the admin panel (unlike
| dictionary.php): form attributes and notices, API and social login messages.
| Used as __('site.{group}.{key}').
|
*/

return [

    // New-order notification sent to the managers' Telegram chat.
    'telegram_order' => [
        'title'    => 'New order #:number',
        'customer' => 'Customer',
        'phone'    => 'Phone',
        'email'    => 'Email',
        'comment'  => 'Comment',
        'items'    => 'Items',
        'discount' => 'Discount',
        'total'    => 'Total',
    ],

    'feedback' => [
        'name'    => 'Name',
        'email'   => 'Email',
        'phone'   => 'Phone',
        'message' => 'Message',
        'saved'   => 'Feedback has been saved.',
    ],

    'news' => [
        'back' => 'All news',
    ],

    'subscribe' => [
        'email'   => 'Email',
        'saved'   => 'Thank you for subscribing!',
        'already' => 'This email is already subscribed.',
    ],

    'api' => [
        'validation_error'   => 'Validation error.',
        'unauthenticated'    => 'Unauthenticated.',
        'forbidden'          => 'Forbidden.',
        'resource_not_found' => 'Resource not found.',
        'to_many_requests'   => 'Too many requests.',
        'login_success'      => 'Login successful.',
        'register_success'   => 'Registration successful.',
        'logout_success'     => 'Logged out.',
    ],

    'social' => [
        'section_title'                        => 'Social Accounts',
        'section_subtitle'                     => 'Connect your social accounts to sign in faster.',
        'connect'                              => 'Connect',
        'disconnect'                           => 'Disconnect',
        'connected'                            => 'Connected (:identifier)',
        'disconnected'                         => 'Social account disconnected.',
        'auth_failed'                          => 'Social authentication failed. Please try again.',
        'cannot_disconnect_last'               => 'Cannot disconnect the only login method. Add a password or email first.',
        'already_connected_to_another_account' => 'This social account is already connected to another account.',
        'sign_in_with'                         => 'Sign in with :provider',
        'google'                               => 'Google',
        'facebook'                             => 'Facebook',
        'telegram'                             => 'Telegram',
    ],

];
