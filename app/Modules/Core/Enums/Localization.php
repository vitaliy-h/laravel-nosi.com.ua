<?php

namespace App\Modules\Core\Enums;

enum Localization: string
{
    case EN = 'en';
    case UK = 'uk';

    public function label(): string
    {
        return match ($this) {
            self::EN => 'English',
            self::UK => 'Ukrainian',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($case) => [$case->value => $case->label()])
            ->toArray();
    }
}
