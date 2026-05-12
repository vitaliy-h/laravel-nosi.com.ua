<?php

namespace App\Modules\Core\Enums;

enum LogSourceEnum: string
{
    case ADMIN_PANEL = 'admin_panel';
    case API = 'api';
    case CONSOLE = 'console';
    case SYSTEM = 'system';
    case CLIENT = 'client';
}
