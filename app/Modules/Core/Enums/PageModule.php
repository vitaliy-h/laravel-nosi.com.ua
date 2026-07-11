<?php

namespace App\Modules\Core\Enums;

use App\Modules\Core\Traits\Enums\HasEnumHelpers;

enum PageModule: string
{
    use HasEnumHelpers;

    case INDEX = 'index';
    case TEXT = 'text_page';
    case NEWS = 'news';
    case FEEDBACK = 'feedback';
    case SHOP = 'shop';
    case SHOP_CART = 'shop_cart';
    case AUTH = 'auth';
    case PROFILE = 'profile';
    case GEO = 'geo';

    public function isEnabled(): bool
    {
        return match ($this) {
            self::GEO       => config('modules.geo', true),
            self::SHOP      => config('modules.shop', true),
            self::SHOP_CART => config('modules.shop', true),
            self::NEWS      => config('modules.news', true),
            self::FEEDBACK  => config('modules.feedback', true),
            self::AUTH      => config('modules.auth', true),
            self::PROFILE   => config('modules.auth', true),
            default         => true,
        };
    }

    public static function active(): array
    {
        return array_values(array_filter(self::cases(), fn($case) => $case->isEnabled()));
    }

    public function label(): string
    {
        return match ($this) {
            self::INDEX => 'Main Page',
            self::TEXT => 'Text Page',
            self::NEWS => 'News',
            self::FEEDBACK => 'Feedback',
            self::SHOP => 'Shop',
            self::SHOP_CART => 'Shop cart',
            self::AUTH => 'Auth',
            self::PROFILE => 'Profile',
            self::GEO => 'Geo',
        };
    }

}
