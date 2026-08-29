<?php

namespace Tests\Feature\Filament\LetterTemplates;

use App\Filament\Resources\LetterTemplates\Pages\ManageLetterTemplates;
use App\Modules\User\Models\User;
use App\Modules\Notification\Enums\LetterEnum;
use App\Modules\Notification\Models\LetterTemplate;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LetterTemplateResourceTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = $this->actingAsAdmin();
        $this->actingAs($this->admin);
    }

    public function test_list_renders_table(): void
    {
        LetterTemplate::factory()->create(['key' => LetterEnum::FEEDBACK]);

        Livewire::test(ManageLetterTemplates::class)
            ->assertSuccessful();
    }

    public function test_list_can_search_by_subject(): void
    {
        $target = LetterTemplate::factory()->create([
            'key'     => LetterEnum::FEEDBACK,
            'subject' => 'Unique Subject Line',
        ]);
        $other = LetterTemplate::factory()->create([
            'key'     => LetterEnum::PASSWORD_RESET,
            'subject' => 'Another Subject',
        ]);

        Livewire::test(ManageLetterTemplates::class)
            ->searchTable('Unique Subject Line')
            ->assertCanSeeTableRecords([$target])
            ->assertCanNotSeeTableRecords([$other]);
    }

    public function test_list_can_search_by_sender(): void
    {
        $target = LetterTemplate::factory()->create([
            'key'    => LetterEnum::FEEDBACK,
            'sender' => 'find@sender.com',
        ]);
        $other = LetterTemplate::factory()->create([
            'key'    => LetterEnum::PASSWORD_RESET,
            'sender' => 'other@sender.com',
        ]);

        Livewire::test(ManageLetterTemplates::class)
            ->searchTable('find@sender.com')
            ->assertCanSeeTableRecords([$target])
            ->assertCanNotSeeTableRecords([$other]);
    }

    public function test_create_letter_template_with_valid_data(): void
    {
        Livewire::test(ManageLetterTemplates::class)
            ->callAction(TestAction::make(CreateAction::getDefaultName())->table(), data: [
                'key'     => LetterEnum::FEEDBACK->value,
                'subject' => 'Welcome Email',
            ])
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('letter_templates', ['subject' => 'Welcome Email']);
    }

    public function test_create_letter_template_requires_key(): void
    {
        Livewire::test(ManageLetterTemplates::class)
            ->callAction(TestAction::make(CreateAction::getDefaultName())->table(), data: [
                'subject' => 'No Key Template',
            ])
            ->assertHasFormErrors(['key']);
    }

    public function test_create_letter_template_requires_subject(): void
    {
        Livewire::test(ManageLetterTemplates::class)
            ->callAction(TestAction::make(CreateAction::getDefaultName())->table(), data: [
                'key' => LetterEnum::FEEDBACK->value,
            ])
            ->assertHasFormErrors(['subject']);
    }

    public function test_create_letter_template_requires_unique_key(): void
    {
        LetterTemplate::factory()->create(['key' => LetterEnum::FEEDBACK]);

        Livewire::test(ManageLetterTemplates::class)
            ->callAction(TestAction::make(CreateAction::getDefaultName())->table(), data: [
                'key'     => LetterEnum::FEEDBACK->value,
                'subject' => 'Duplicate Key',
            ])
            ->assertHasFormErrors(['key']);
    }

    public function test_edit_letter_template_with_valid_data(): void
    {
        $template = LetterTemplate::factory()->create([
            'key'     => LetterEnum::FEEDBACK,
            'subject' => 'Old Subject',
        ]);

        Livewire::test(ManageLetterTemplates::class)
            ->callAction(TestAction::make(EditAction::getDefaultName())->table($template), data: [
                'subject' => 'Updated Subject',
            ])
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('letter_templates', ['id' => $template->id, 'subject' => 'Updated Subject']);
    }

    public function test_delete_letter_template(): void
    {
        $template = LetterTemplate::factory()->create(['key' => LetterEnum::FEEDBACK]);

        Livewire::test(ManageLetterTemplates::class)
            ->callAction(TestAction::make(DeleteAction::getDefaultName())->table($template))
            ->assertSuccessful();

        $this->assertModelMissing($template);
    }
}
