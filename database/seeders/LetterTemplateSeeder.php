<?php

namespace Database\Seeders;

use App\Modules\Core\Enums\LetterEnum;
use App\Modules\Core\Models\LetterTemplate;
use Illuminate\Database\Seeder;

class LetterTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (LetterEnum::cases() as $case) {
            $data = __("letter_templates.{$case->value}");
            if (!LetterTemplate::whereKey($case)->exists()) {
                LetterTemplate::create(
                    [
                        'key' => $case->value,
                        'subject' => $data['subject'],
                        'body' => $data['body'],
                    ]
                );
            }
        }
    }
}
