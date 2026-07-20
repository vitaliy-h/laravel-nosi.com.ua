<?php

namespace App\Modules\Notification\Enums;

use App\Modules\Core\Traits\Enums\HasEnumHelpers;

enum LetterEnum: string
{
    use HasEnumHelpers;

    case FEEDBACK = 'feedback';
    case PASSWORD_RESET = 'password_reset';
    case REGISTRATION = 'registration';
    case ORDER_CREATED = 'order_created';
}
