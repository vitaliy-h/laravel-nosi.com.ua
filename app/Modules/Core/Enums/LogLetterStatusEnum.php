<?php

namespace App\Modules\Core\Enums;

use App\Modules\Core\Traits\Enums\HasEnumHelpers;

enum LogLetterStatusEnum: string
{
    use HasEnumHelpers;

    case QUEUED = 'queued';

    case SENT = 'sent';

    case FAILED = 'failed';
}
