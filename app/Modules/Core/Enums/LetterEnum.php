<?php

namespace App\Modules\Core\Enums;

use App\Modules\Core\Traits\Enums\HasEnumHelpers;

enum LetterEnum: string
{
    use HasEnumHelpers;

    case FEEDBACK = 'feedback';
    case PASSWORD_RESET = 'password_reset';
}
