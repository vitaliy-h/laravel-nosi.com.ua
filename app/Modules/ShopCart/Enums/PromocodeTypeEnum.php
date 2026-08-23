<?php

namespace App\Modules\ShopCart\Enums;

use App\Modules\Core\Traits\Enums\HasEnumHelpers;

enum PromocodeTypeEnum: string
{
    use HasEnumHelpers;

    case FIXED = 'fixed';
    case PERCENT = 'percent';

    public function label(): string
    {
        return match ($this) {
            self::FIXED => 'Fixed amount',
            self::PERCENT => 'Percent',
        };
    }
}
