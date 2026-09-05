<?php

namespace Tests\Feature\Filament\SmsTemplates;

use App\Filament\Resources\SmsTemplates\Pages\ManageSmsTemplates;
use App\Modules\Notification\Enums\SmsEnum;
use App\Modules\Notification\Models\SmsTemplate;
use App\Modules\User\Models\User;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SmsTemplateResourceTest extends TestCase
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
        SmsTemplate::factory()->create(['key' => SmsEnum::VERIFICATION]);

        Livewire::test(ManageSmsTemplates::class)
            ->assertSuccessful();
    }

    public function test_list_can_search_by_subject(): void
    {
        $target = SmsTemplate::factory()->create([
            'key'     => SmsEnum::VERIFICATION,
            'subject' => 'Unique SMS Subject',
        ]);

        Livewire::test(ManageSmsTemplates::class)
            ->searchTable('Unique SMS Subject')
            ->assertCanSeeTableRecords([$target]);
    }

    public function test_list_can_search_by_body(): void
    {
        $target = SmsTemplate::factory()->create([
            'key'  => SmsEnum::VERIFICATION,
            'body' => 'Your verification code is {code}',
        ]);

        Livewire::test(ManageSmsTemplates::class)
            ->searchTable('verification code')
            ->assertCanSeeTableRecords([$target]);
    }

    public function test_create_sms_template_with_valid_data(): void
    {
        Livewire::test(ManageSmsTemplates::class)
            ->callAction(TestAction::make(CreateAction::getDefaultName())->table(), data: [
                'key'  => SmsEnum::VERIFICATION->value,
                'body' => 'Your code: {code}',
            ])
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('sms_templates', ['key' => SmsEnum::VERIFICATION->value]);
    }

    public function test_create_sms_template_requires_key(): void
    {
        Livewire::test(ManageSmsTemplates::class)
            ->callAction(TestAction::make(CreateAction::getDefaultName())->table(), data: [
                'body' => 'Your code: {code}',
            ])
            ->assertHasFormErrors(['key']);
    }

    public function test_create_sms_template_requires_unique_key(): void
    {
        SmsTemplate::factory()->create(['key' => SmsEnum::VERIFICATION]);

        Livewire::test(ManageSmsTemplates::class)
            ->callAction(TestAction::make(CreateAction::getDefaultName())->table(), data: [
                'key'  => SmsEnum::VERIFICATION->value,
                'body' => 'Duplicate',
            ])
            ->assertHasFormErrors(['key']);
    }

    public function test_edit_sms_template_with_valid_data(): void
    {
        $template = SmsTemplate::factory()->create([
            'key'  => SmsEnum::VERIFICATION,
            'body' => 'Old body',
        ]);

        Livewire::test(ManageSmsTemplates::class)
            ->callAction(TestAction::make(EditAction::getDefaultName())->table($template), data: [
                'body' => 'Updated body with {code}',
            ])
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('sms_templates', ['id' => $template->id, 'body' => 'Updated body with {code}']);
    }

    public function test_delete_sms_template(): void
    {
        $template = SmsTemplate::factory()->create(['key' => SmsEnum::VERIFICATION]);

        Livewire::test(ManageSmsTemplates::class)
            ->callAction(TestAction::make(DeleteAction::getDefaultName())->table($template))
            ->assertSuccessful();

        $this->assertModelMissing($template);
    }
}
