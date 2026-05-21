<?php

return [

    \App\Modules\Core\Enums\LetterEnum::FEEDBACK->value => [
        'subject' => 'New feedback',
        'body' => '
            <p>User {{ $user_name}} {{ $user_email}} added new feedback.</p>

            <p>Message:{{ $message }}</p>
        ',
    ],

];
