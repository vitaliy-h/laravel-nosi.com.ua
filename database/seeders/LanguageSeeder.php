<?php

namespace Database\Seeders;

use App\Modules\Core\Enums\Localization;
use App\Modules\Core\Facades\Trans;
use App\Modules\Core\Models\Language;
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

            $enum = Localization::tryFrom($locale);

            if (!$enum) {
                continue;
            }

            Language::query()->updateOrCreate(
                [
                    'localization' => $enum,
                ],
                [
                    'title' => $enum->label(),
                    'dictionary' => $dictionary,
                    'rank' => $rank--,
                    'display' => true,
                ]
            );
            Trans::clearCache($locale);
        }
    }
}
