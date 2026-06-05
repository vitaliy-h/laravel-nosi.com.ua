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
    case AUTH = 'auth';
    case PROFILE = 'profile';
    case GEO = 'geo';

    public function label(): string
    {
        return match ($this) {
            self::INDEX => 'Main Page',
            self::TEXT => 'Text Page',
            self::NEWS => 'News',
            self::FEEDBACK => 'Feedback',
            self::SHOP => 'Shop',
            self::AUTH => 'Auth',
            self::PROFILE => 'Profile',
            self::GEO => 'Geo',
        };
    }

}
