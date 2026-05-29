<?php

namespace App\Modules\Translation\Dtos;

use App\Modules\Translation\Enums\Localization;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\EnumCast;
use Spatie\LaravelData\Data;

class TranslationRequestData extends Data
{
    public function __construct(
        #[WithCast(EnumCast::class, Localization::class)]
        public readonly Localization $locale,
        public readonly ?string      $group = null,
    )
    {
    }
}
