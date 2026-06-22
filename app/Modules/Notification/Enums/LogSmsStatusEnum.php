<?php

namespace App\Modules\Notification\Enums;

use App\Modules\Core\Traits\Enums\HasEnumHelpers;

enum LogSmsStatusEnum: string
{
    use HasEnumHelpers;

    case QUEUED = 'queued';
    case SENT = 'sent';
    case FAILED = 'failed';

    public function color(): string
    {
        return match ($this) {
            self::SENT => 'success',
            self::FAILED => 'danger',
            default => 'warning',
        };
    }
}
