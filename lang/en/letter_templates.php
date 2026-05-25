<?php

return [

    \App\Modules\Core\Enums\LetterEnum::FEEDBACK->value => [
        'subject' => 'New feedback',
        'body' => '
            <p>User {{ $user_name}} {{ $user_email}} added new feedback.</p>

            <p>Message:{{ $message }}</p>
        ',
    ],

    \App\Modules\Core\Enums\LetterEnum::PASSWORD_RESET->value => [
        'subject' => 'Reset password',
        'body' => '
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

];
