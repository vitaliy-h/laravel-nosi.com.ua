<?php

return [

    \App\Modules\Core\Enums\LetterEnum::FEEDBACK->value => [
        'subject' => 'New feedback',
        'body' => '
            <p>User, {{ $user_name}} added new feedback.</p>

            <p>Message:{{ $message }}</p>
        ',
    ],

];
