<?php

namespace App\Modules\Core\Enums;

enum LogLetterStatusEnum: string
{
    case QUEUED = 'queued';

    case SENT = 'sent';

    case FAILED = 'failed';
}
