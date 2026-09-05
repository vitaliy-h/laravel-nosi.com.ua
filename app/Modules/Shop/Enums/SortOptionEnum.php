<?php

namespace App\Modules\Shop\Enums;

use App\Modules\Core\Traits\Enums\HasEnumHelpers;

enum SortOptionEnum: string
{
    use HasEnumHelpers;

    case NEWEST = 'newest';
    case PRICE_ASC = 'price_asc';
    case PRICE_DESC = 'price_desc';
    case TITLE_ASC = 'title_asc';
    case TITLE_DESC = 'title_desc';

    public function label(): string
    {
        return match ($this) {
            self::NEWEST     => t('shop.sort_option_newest'),
            self::PRICE_ASC  => t('shop.sort_option_price_asc'),
            self::PRICE_DESC => t('shop.sort_option_price_desc'),
            self::TITLE_ASC  => t('shop.sort_option_title_asc'),
            self::TITLE_DESC => t('shop.sort_option_title_desc'),
        };
    }

    /**
     * Column and direction to order the products query by.
     *
     * @return array{0: string, 1: 'asc'|'desc'}
     */
    public function orderBy(): array
    {
        return match ($this) {
            self::NEWEST     => ['id', 'desc'],
            self::PRICE_ASC  => ['price', 'asc'],
            self::PRICE_DESC => ['price', 'desc'],
            self::TITLE_ASC  => ['title', 'asc'],
            self::TITLE_DESC => ['title', 'desc'],
        };
    }
}
