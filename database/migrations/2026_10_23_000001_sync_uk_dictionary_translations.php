<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The Ukrainian dictionary in the database was created as a copy of the English
 * one, so most site texts stayed English. This brings in the translations from
 * lang/uk/dictionary.php: a key gets the file value when it is missing in the
 * database or still holds the English original; a text already edited in the
 * admin panel stays. Keys the file no longer has are dropped.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('languages')) {
            return;
        }

        $row = DB::table('languages')->where('localization', 'uk')->first();
        $ukFile = lang_path('uk/dictionary.php');
        $enFile = lang_path('en/dictionary.php');

        if ($row === null || !is_file($ukFile) || !is_file($enFile)) {
            return;
        }

        $uk = Arr::dot($this->normalize(require $ukFile));
        $en = Arr::dot($this->normalize(require $enFile));
        $current = Arr::dot(json_decode((string) $row->dictionary, true) ?: []);

        // Built from the file's keys only: keys the file no longer has (leftovers of
        // the starter) are not in the admin form either, so they are dropped.
        $result = [];

        foreach ($uk as $key => $value) {
            $edited = array_key_exists($key, $current) && $current[$key] !== ($en[$key] ?? null);
            $result[$key] = $edited ? $current[$key] : $value;
        }

        DB::table('languages')->where('id', $row->id)->update([
            'dictionary' => json_encode(Arr::undot($result), JSON_UNESCAPED_UNICODE),
            'updated_at' => now(),
        ]);

        // Language names were seeded in English.
        DB::table('languages')->where('localization', 'uk')->where('title', 'Ukrainian')->update(['title' => 'Українська']);
        DB::table('languages')->where('localization', 'en')->where('title', 'English')->update(['title' => 'Англійська']);

        Cache::forget('translations.uk');
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
