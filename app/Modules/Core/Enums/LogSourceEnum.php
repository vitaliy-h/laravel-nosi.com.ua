<?php

namespace App\Modules\Core\Enums;

use App\Modules\Core\Traits\Enums\HasEnumHelpers;

enum LogSourceEnum: string
{
    use HasEnumHelpers;

    case ADMIN_PANEL = 'admin_panel';
    case API = 'api';
    case CONSOLE = 'console';
    case SYSTEM = 'system';
    case CLIENT = 'client';
}
