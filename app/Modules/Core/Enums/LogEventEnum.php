<?php

namespace App\Modules\Core\Enums;

use App\Modules\Core\Traits\Enums\HasEnumHelpers;

enum LogEventEnum: string
{
    use HasEnumHelpers;

    case CREATED = 'created';
    case UPDATED = 'updated';
    case DELETED = 'deleted';
}
