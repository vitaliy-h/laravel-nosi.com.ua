<?php

use App\Modules\Notification\Enums\SmsEnum;

return [

    SmsEnum::VERIFICATION->value => [
        'subject' => 'Verification code',
        'body' => 'Your verification code is: {{ $code }}.',
    ],

];
