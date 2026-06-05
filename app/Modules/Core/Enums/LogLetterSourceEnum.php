<?php

namespace App\Modules\Core\Enums;

use App\Modules\Core\Traits\Enums\HasEnumHelpers;

enum LogLetterSourceEnum: string
{
    use HasEnumHelpers;

    case CONSOLE = 'console';

    case ADMIN = 'admin';

    case SYSTEM = 'system';
}
