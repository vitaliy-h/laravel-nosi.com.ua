<?php

namespace App\Modules\Shop\Enums;

use App\Modules\Core\Traits\Enums\HasEnumHelpers;

enum SortOptionEnum: string
{
    use HasEnumHelpers;

    case NEWEST = 'newest';
    case PRICE_ASC = 'price_asc';
    case PRICE_DESC = 'price_desc';

    public function label(): string
    {
        return match ($this) {
            self::NEWEST => __('dictionary.shop.sort_option_newest'),
            self::PRICE_ASC => __('dictionary.shop.sort_option_price_asc'),
            self::PRICE_DESC => __('dictionary.shop.sort_option_price_desc'),
        };
    }
}
