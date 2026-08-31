<?php

namespace App\Modules\Core\Enums;

use App\Modules\Core\Traits\Enums\HasEnumHelpers;

enum AuthLogResultEnum: string
{
    use HasEnumHelpers;

    case SUCCESS = 'success';
    case FAILURE = 'failure';
    case BLOCKED = 'blocked';

    public function label(): string
    {
        return __("admin.auth_log.results.{$this->value}");
    }

    public function color(): string
    {
        return match ($this) {
            self::SUCCESS => 'success',
            self::FAILURE => 'warning',
            self::BLOCKED => 'danger',
        };
    }
}
