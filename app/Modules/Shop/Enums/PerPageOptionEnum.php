<?php

namespace App\Modules\Shop\Enums;

use App\Modules\Core\Traits\Enums\HasEnumHelpers;

enum PerPageOptionEnum: int
{
    use HasEnumHelpers;

    case TWELVE = 12;
    case TWENTY_FOUR = 24;
    case FORTY_EIGHT = 48;
    case NINETY_SIX = 96;

    public function label(): string
    {
        return __('dictionary.shop.per_page_option', ['count' => $this->value]);
    }

    public static function default(): self
    {
        return self::TWELVE;
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
