<?php

namespace Database\Factories;

use App\Modules\Translation\Enums\Localization;
use App\Modules\Translation\Models\Language;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Language>
 */
class LanguageFactory extends Factory
{
    protected $model = Language::class;

    public function definition(): array
    {
        $localization = fake()->unique()->randomElement(Localization::cases());

        return [
            'title'        => $localization->label(),
            'localization' => $localization,
            'rank'         => fake()->numberBetween(0, 10),
            'display'      => true,
            'dictionary'   => $this->dictionaryFor($localization),
        ];
    }

    public function forLocalization(Localization $localization): static
    {
        return $this->state(fn(array $attributes) => [
            'localization' => $localization,
            'title'        => $localization->label(),
            'dictionary'   => $this->dictionaryFor($localization),
        ]);
    }

    private function dictionaryFor(Localization $localization): array
    {
        $path = lang_path($localization->value . '/dictionary.php');

        if (!file_exists($path)) {
            $path = lang_path(Localization::EN->value . '/dictionary.php');
        }

        if (!file_exists($path)) {
            return [];
        }

        return $this->normalize(require $path);
    }

    private function normalize(array $dictionary): array
    {
        $result = [];

        foreach ($dictionary as $key => $value) {
            $cleanKey = explode(':', $key)[0];
            $result[$cleanKey] = is_array($value) ? $this->normalize($value) : $value;
        }

        return $result;
    }
}
