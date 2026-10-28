<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Brings the key set of every language in the database in line with
 * lang/{locale}/dictionary.php (lang/en for a locale without its own file),
 * same as 2026_10_28_000001: adds header.back and header.all_in_category —
 * the «Назад» and «Усі товари» of the mobile menu. A text already in the database stays as it is.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('languages')) {
            return;
        }

        foreach (DB::table('languages')->get(['id', 'localization', 'dictionary']) as $row) {
            $file = lang_path("{$row->localization}/dictionary.php");
            $file = is_file($file) ? $file : lang_path('en/dictionary.php');

            if (!is_file($file)) {
                continue;
            }

            $template = Arr::dot($this->normalize(require $file));
            $current = Arr::dot(json_decode((string) $row->dictionary, true) ?: []);

            $result = [];

            foreach ($template as $key => $value) {
                $result[$key] = array_key_exists($key, $current) ? $current[$key] : $value;
            }

            DB::table('languages')->where('id', $row->id)->update([
                'dictionary' => json_encode(Arr::undot($result), JSON_UNESCAPED_UNICODE),
                'updated_at' => now(),
            ]);

            Cache::forget("translations.{$row->localization}");
        }
    }

    public function down(): void
    {
        // Translations are content: nothing to roll back.
    }

    /**
     * Drop the form suffixes (`title:input6:br` → `title`), as LanguageSeeder does.
     */
    private function normalize(array $dictionary): array
    {
        $normalized = [];

        foreach ($dictionary as $key => $value) {
            $normalized[explode(':', (string) $key)[0]] = is_array($value) ? $this->normalize($value) : $value;
        }

        return $normalized;
    }
};
