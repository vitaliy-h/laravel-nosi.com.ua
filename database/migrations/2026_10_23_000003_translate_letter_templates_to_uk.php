<?php

use App\Modules\Notification\Enums\LetterEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The letter templates were seeded from lang/en/letter_templates.php before the
 * Ukrainian file existed, so customers got English letters. A template still
 * holding the English original gets the text from lang/uk/letter_templates.php;
 * a template already edited in the admin panel stays as it is.
 */
return new class extends Migration
{
    public function up(): void
    {
        $ukFile = lang_path('uk/letter_templates.php');
        $enFile = lang_path('en/letter_templates.php');

        if (!Schema::hasTable('letter_templates') || config('app.locale') !== 'uk' || !is_file($ukFile) || !is_file($enFile)) {
            return;
        }

        $uk = require $ukFile;
        $en = require $enFile;

        foreach (LetterEnum::cases() as $case) {
            $row = DB::table('letter_templates')->where('key', $case->value)->first();

            if ($row === null || !isset($uk[$case->value], $en[$case->value])) {
                continue;
            }

            $changes = [];

            foreach (['subject', 'body'] as $field) {
                if ($this->same($row->{$field}, $en[$case->value][$field])) {
                    $changes[$field] = $uk[$case->value][$field];
                }
            }

            if ($changes !== []) {
                DB::table('letter_templates')->where('id', $row->id)->update($changes + ['updated_at' => now()]);
            }
        }
    }

    public function down(): void
    {
        // Templates are content: nothing to roll back.
    }

    /**
     * Whitespace does not count: the admin editor reflows the HTML on save.
     */
    private function same(?string $stored, string $original): bool
    {
        $squash = fn (string $text): string => trim(preg_replace('/\s+/u', ' ', $text));

        return $squash((string) $stored) === $squash($original);
    }
};
