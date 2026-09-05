<?php

namespace App\Modules\Page\Enums;

use App\Modules\Core\Support\ModuleGate;
use App\Modules\Core\Traits\Enums\HasEnumHelpers;

enum PageModule: string
{
    use HasEnumHelpers;

    case INDEX = 'index';
    case TEXT = 'page_text';
    case NEWS = 'news';
    case FEEDBACK = 'feedback';
    case SHOP = 'shop';
    case SHOP_CART = 'shop_cart';
    case AUTH = 'user_auth';
    case PROFILE = 'profile';
    case GEO = 'geo';

    public function isEnabled(): bool
    {
        return ModuleGate::isEnabled($this->value);
    }

    public static function active(): array
    {
        return array_values(array_filter(self::cases(), fn ($case) => $case->isEnabled()));
    }

    public static function activeOptions(): array
    {
        return collect(self::active())
            ->mapWithKeys(fn (self $case) => [$case->value => $case->label()])
            ->toArray();
    }

    public function label(): string
    {
        return match ($this) {
            self::INDEX     => 'Main Page',
            self::TEXT      => 'Text Page',
            self::NEWS      => 'News',
            self::FEEDBACK  => 'Feedback',
            self::SHOP      => 'Shop',
            self::SHOP_CART => 'Shop cart',
            self::AUTH      => 'Auth',
            self::PROFILE   => 'Profile',
            self::GEO       => 'Geo',
        };
    }
}
