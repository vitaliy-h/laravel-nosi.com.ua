<?php

namespace App\Modules\Translation\Services;

use App\Modules\Translation\Enums\Localization;
use App\Modules\Translation\Queries\GetTranslationsByLocaleQuery;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Lang;

class TranslationService
{
    private array $dictionaries = [];

    public function __construct(private readonly GetTranslationsByLocaleQuery $getTranslationsByLocale) {}

    public function get(string $key, array $replace = []): string
    {
        $locale = app()->getLocale();
        $fallbackLocale = config('app.fallback_locale');
        // current locale
        $translation = $this->findTranslation(
            locale: $locale,
            key: $key,
        );

        // fallback locale
        if ($translation === null && $fallbackLocale !== $locale) {
            $translation = $this->findTranslation(
                locale: $fallbackLocale,
                key: $key,
            );
        }

        if ($translation === null) {
            $translation = $this->getDictionaryTranslation($key);
        }

        if ($translation === null) {
            return '';
        }

        // replacements
        foreach ($replace as $search => $value) {
            $translation = str_replace(
                ':' . $search,
                $value,
                $translation,
            );
        }

        return $translation;
    }

    protected function findTranslation(string $locale, string $key): ?string
    {
        $dictionary = $this->getDictionary($locale);

        // A key cleared in the admin is stored as null — it is still "set", so it
        // must stay empty instead of falling back to the file value.
        if (!Arr::has($dictionary, $key)) {
            return null;
        }

        $value = Arr::get($dictionary, $key);

        return is_array($value) ? null : (string) $value;
    }

    public function getDictionary(string $locale): array
    {
        if (isset($this->dictionaries[$locale])) {
            return $this->dictionaries[$locale];
        }

        return $this->dictionaries[$locale] = Cache::remember(
            $this->getCacheKey($locale),
            now()->addDay(),
            function () use ($locale) {
                return $this->getTranslationsByLocale->handle($locale);
            },
        );
    }

    public function clearCache(Localization|string $locale): void
    {
        if ($locale instanceof Localization) {
            $locale = $locale->value;
        }

        unset($this->dictionaries[$locale]);
        Cache::forget($this->getCacheKey($locale));
    }

    protected function getCacheKey(string $locale): string
    {
        return "translations.$locale";
    }

    public function getDictionaryTranslation(string $key): ?string
    {
        $translations = Lang::get('dictionary');

        if (!is_array($translations)) {
            return null;
        }

        $flatten = Arr::dot($translations);

        // exact match
        if (isset($flatten[$key])) {
            return $flatten[$key];
        }

        // search with metadata, example terms:input12
        foreach ($flatten as $translationKey => $value) {

            if (str($translationKey)->startsWith($key . ':')) {
                return $value;
            }
        }

        return null;
    }
}
