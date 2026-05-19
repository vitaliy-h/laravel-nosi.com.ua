<?php

namespace App\Modules\Core\Enums;

enum LogLetterSourceEnum: string
{
    case CONSOLE = 'console';

    case ADMIN = 'admin';

    case SYSTEM = 'system';
}
