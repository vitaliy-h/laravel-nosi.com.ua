<?php

namespace Database\Seeders;

use App\Modules\Translation\Enums\Localization;
use App\Modules\Translation\Facade\Trans;
use App\Modules\Translation\Models\Language;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $langPath = lang_path();

        $rank = 100;

        foreach (File::directories($langPath) as $dir) {

            $locale = basename($dir);

            $file = $dir . '/dictionary.php';

            if (!File::exists($file)) {
                continue;
            }

            $dictionary = require $file;

            $dictionary = $this->normalizeDictionary(
                $dictionary
            );

            $enum = Localization::tryFrom($locale);

            if (!$enum) {
                continue;
            }

            Language::query()->updateOrCreate(
                [
                    'localization' => $enum,
                ],
                [
                    'title'      => $enum->label(),
                    'dictionary' => $dictionary,
                    'rank'       => $rank--,
                    'display'    => true,
                ]
            );

            Trans::clearCache($locale);
        }
    }

    protected function normalizeDictionary(array $dictionary): array
    {

        $normalized = [];

        foreach ($dictionary as $key => $value) {

            $cleanKey = $this->cleanKey($key);

            if (is_array($value)) {

                $normalized[$cleanKey] = $this->normalizeDictionary(
                    $value
                );

                continue;
            }

            $normalized[$cleanKey] = $value;
        }

        return $normalized;
    }

    protected function cleanKey(string $key): string
    {

        return explode(':', $key)[0];
    }
}
