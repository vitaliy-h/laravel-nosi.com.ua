<?php

namespace App\Modules\Core\Enums;

enum LogEventEnum: string
{
    case CREATED = 'created';
    case UPDATED = 'updated';
    case DELETED = 'deleted';
}
