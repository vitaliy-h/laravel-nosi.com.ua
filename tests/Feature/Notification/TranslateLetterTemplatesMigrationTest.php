<?php

namespace Tests\Feature\Notification;

use App\Modules\Notification\Enums\LetterEnum;
use App\Modules\Notification\Models\LetterTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TranslateLetterTemplatesMigrationTest extends TestCase
{
    use RefreshDatabase;

    private function runMigration(): void
    {
        (require database_path('migrations/2026_10_23_000003_translate_letter_templates_to_uk.php'))->up();
    }

    private function english(LetterEnum $key): array
    {
        return (require lang_path('en/letter_templates.php'))[$key->value];
    }

    public function test_a_template_still_in_english_gets_the_ukrainian_text(): void
    {
        config(['app.locale' => 'uk']);
        $en = $this->english(LetterEnum::ORDER_CREATED);
        // The admin editor reflows the HTML: whitespace must not count as an edit.
        LetterTemplate::create(['key' => LetterEnum::ORDER_CREATED, 'subject' => $en['subject'], 'body' => preg_replace('/\s+/', ' ', $en['body'])]);

        $this->runMigration();

        $uk = (require lang_path('uk/letter_templates.php'))[LetterEnum::ORDER_CREATED->value];
        $template = LetterTemplate::where('key', LetterEnum::ORDER_CREATED)->first();

        $this->assertSame($uk['subject'], $template->subject);
        $this->assertSame($uk['body'], $template->body);
    }

    public function test_a_text_edited_in_the_admin_panel_stays(): void
    {
        config(['app.locale' => 'uk']);
        $en = $this->english(LetterEnum::FEEDBACK);
        LetterTemplate::create(['key' => LetterEnum::FEEDBACK, 'subject' => 'Свій заголовок', 'body' => $en['body']]);

        $this->runMigration();

        $template = LetterTemplate::where('key', LetterEnum::FEEDBACK)->first();

        $this->assertSame('Свій заголовок', $template->subject);
        $this->assertStringContainsString('Нове повідомлення з сайту', $template->body, 'the untouched body is still translated');
    }

    public function test_an_english_site_keeps_english_letters(): void
    {
        config(['app.locale' => 'en']);
        $en = $this->english(LetterEnum::PASSWORD_RESET);
        LetterTemplate::create(['key' => LetterEnum::PASSWORD_RESET, 'subject' => $en['subject'], 'body' => $en['body']]);

        $this->runMigration();

        $this->assertSame($en['subject'], LetterTemplate::where('key', LetterEnum::PASSWORD_RESET)->value('subject'));
    }
}
