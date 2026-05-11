<?php

namespace App\Modules\Core\Enums;

enum ActivityLogEventEnum: string
{
    case CREATED = 'created';
    case UPDATED = 'updated';
    case DELETED = 'deleted';
}
