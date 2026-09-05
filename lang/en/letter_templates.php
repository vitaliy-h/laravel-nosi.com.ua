<?php

use App\Modules\Notification\Enums\LetterEnum;

return [

    LetterEnum::FEEDBACK->value => [
        'subject' => 'New feedback',
        'body'    => '
            <p>User {{ $user_name}} {{ $user_email}} added new feedback.</p>

            <p>Message:{{ $message }}</p>
        ',
    ],

    LetterEnum::PASSWORD_RESET->value => [
        'subject' => 'Reset password',
        'body'    => '
           <h2>Password recovery</h2>

        <p>
        You received this email because there was a request to reset your password.
        </p>

        <p>
        <a href="{{ $reset_url }}">
        Change password
        </a>
        </p>

        <p>
        If you did not make this request, simply ignore the letter.
        </p>
        ',
    ],

    LetterEnum::REGISTRATION->value => [
        'subject' => 'Welcome! Your account details',
        'body'    => '
        <h2>Welcome, {{ $user_name }}</h2>

        <p>
        Your account has been created.
        </p>

        <p>
        Email: {{ $user_email }}<br>
        Password: {{ $user_password }}
        </p>

        <p>
        <a href="{{ $login_url }}">
        Log in
        </a>
        </p>
        ',
    ],

    LetterEnum::ORDER_CREATED->value => [
        'subject' => 'Order #{{ $order_number }} created',
        'body'    => '
        <h2>Thank you for your order</h2>

        <p>
        Order number: {{ $order_number }}<br>
        Total amount: {{ $total_amount }}
        </p>

        <p>
        <a href="{{ $order_url }}">
        View your order
        </a>
        </p>
        ',
    ],

];
