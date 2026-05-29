<?php

namespace App\Modules\Translation\Services;

use App\Modules\Translation\Enums\Localization;
use App\Modules\Translation\Repositories\TranslationRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Lang;

readonly class TranslationService
{
    public function __construct(private TranslationRepository $translationRepository)
    {
    }

    /**
     * @param string $key
     * @param array $replace
     * @return string
     */
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

    /**
     * @param string $locale
     * @param string $key
     * @return string|null
     */
    protected function findTranslation(string $locale, string $key): ?string
    {
        $dictionary = $this->getDictionary($locale);

        return data_get($dictionary, $key);
    }

    public function getDictionary(string $locale): array
    {
        return Cache::remember(
            $this->getCacheKey($locale),
            now()->addDay(),
            function () use ($locale) {
                return $this->translationRepository->getByLocale($locale);
            },
        );
    }

    public function clearCache(Localization|string $locale): void
    {
        if ($locale instanceof Localization) {
            $locale = $locale->value;
        }

        Cache::forget(
            $this->getCacheKey($locale)
        );
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
