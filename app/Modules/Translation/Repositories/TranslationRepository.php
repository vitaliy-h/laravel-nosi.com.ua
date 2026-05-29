<?php

namespace App\Modules\Translation\Repositories;

use App\Modules\Translation\Enums\Localization;
use App\Modules\Translation\Models\Language;

class TranslationRepository
{
    public function getByLocale(string|Localization $locale, ?string $group = null): array
    {
        $dictionary = Language::query()
            ->where('localization', $locale)
            ->visible()
            ->value('dictionary') ?? [];
        return $group && @$dictionary[$group] ? $dictionary[$group] : $dictionary;
    }
}
