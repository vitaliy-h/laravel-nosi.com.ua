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
        return __("enums.promocode_type.{$this->value}");
    }
}
