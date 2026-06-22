<?php

namespace App\Modules\Notification\Enums;

use App\Modules\Core\Traits\Enums\HasEnumHelpers;

enum SmsEnum: string
{
    use HasEnumHelpers;

    case VERIFICATION = 'verification';
}
