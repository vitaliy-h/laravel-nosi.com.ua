<?php

namespace App\Modules\Notification\Enums;

use App\Modules\Core\Traits\Enums\HasEnumHelpers;

enum NotificationSourceEnum: string
{
    use HasEnumHelpers;

    case CONSOLE = 'console';
    case ADMIN = 'admin';
    case SYSTEM = 'system';
}
