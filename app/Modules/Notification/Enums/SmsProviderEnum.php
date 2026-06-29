<?php

namespace App\Modules\Notification\Enums;

use App\Modules\Core\Traits\Enums\HasEnumHelpers;

enum SmsProviderEnum: string
{
    use HasEnumHelpers;

    case LOG = 'log';
}
