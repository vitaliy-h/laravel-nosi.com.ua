<?php

namespace App\Modules\Core\Traits\Enums;

trait HasEnumHelpers
{
    public static function options(): array
    {
        return collect(static::cases())
            ->mapWithKeys(fn ($case) => [
                $case->value => method_exists($case, 'label') ? $case->label() : $case->value,
            ])
            ->toArray();
    }
}
