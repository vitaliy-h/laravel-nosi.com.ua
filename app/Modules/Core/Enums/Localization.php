<?php

namespace App\Modules\Core\Enums;

enum Localization: string
{
    case EN = 'en';

    public function label(): string
    {
        return match ($this) {
            self::EN => 'English',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($case) => [$case->value => $case->label()])
            ->toArray();
    }
}
