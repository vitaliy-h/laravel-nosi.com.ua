<?php

namespace App\Modules\Core\Enums;

enum PageModule: string
{
    case INDEX = 'index';
    case TEXT = 'text_page';
    case NEWS = 'news';
    case FEEDBACK = 'feedback';
    case SHOP = 'shop';
    case AUTH = 'auth';

    public function label(): string
    {
        return match ($this) {
            self::INDEX => 'Main Page',
            self::TEXT => 'Text Page',
            self::NEWS => 'News',
            self::FEEDBACK => 'Feedback',
            self::SHOP => 'Shop',
            self::AUTH => 'Auth',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($case) => [$case->value => $case->label()])
            ->toArray();
    }
}
