<?php

namespace App\Modules\Translation\Enums;

use App\Modules\Core\Traits\Enums\HasEnumHelpers;

enum Localization: string
{
    use HasEnumHelpers;
    case EN = 'en';
    case UK = 'uk';

    public function label(): string
    {
        return match ($this) {
            self::EN => 'English',
            self::UK => 'Ukrainian',
        };
    }
}
