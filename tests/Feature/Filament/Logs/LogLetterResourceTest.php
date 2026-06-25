<?php

namespace Tests\Feature\Filament\Logs;

use App\Filament\Resources\LogLetters\Pages\ManageLogLetters;
use App\Modules\Core\Models\User;
use App\Modules\Notification\Enums\LogLetterStatusEnum;
use App\Modules\Notification\Enums\NotificationSourceEnum;
use App\Modules\Notification\Models\LogLetter;
use Filament\Actions\DeleteAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LogLetterResourceTest extends TestCase
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
        LogLetter::factory()->count(3)->create();

        Livewire::test(ManageLogLetters::class)
            ->assertSuccessful();
    }

    public function test_list_can_search_by_receiver(): void
    {
        $target = LogLetter::factory()->create(['receiver' => 'find@example.com']);
        $other  = LogLetter::factory()->create(['receiver' => 'other@example.com']);

        Livewire::test(ManageLogLetters::class)
            ->searchTable('find@example.com')
            ->assertCanSeeTableRecords([$target])
            ->assertCanNotSeeTableRecords([$other]);
    }

    public function test_list_can_search_by_subject(): void
    {
        $target = LogLetter::factory()->create(['subject' => 'Unique Subject Here']);
        $other  = LogLetter::factory()->create(['subject' => 'Another Subject']);

        Livewire::test(ManageLogLetters::class)
            ->searchTable('Unique Subject Here')
            ->assertCanSeeTableRecords([$target])
            ->assertCanNotSeeTableRecords([$other]);
    }

    public function test_list_can_filter_by_status(): void
    {
        $sent   = LogLetter::factory()->create(['status' => LogLetterStatusEnum::SENT]);
        $failed = LogLetter::factory()->failed()->create();

        Livewire::test(ManageLogLetters::class)
            ->filterTable('status', LogLetterStatusEnum::SENT->value)
            ->assertCanSeeTableRecords([$sent])
            ->assertCanNotSeeTableRecords([$failed]);
    }

    public function test_list_can_filter_by_source(): void
    {
        $system = LogLetter::factory()->create(['source' => NotificationSourceEnum::SYSTEM]);
        $admin  = LogLetter::factory()->create(['source' => NotificationSourceEnum::ADMIN]);

        Livewire::test(ManageLogLetters::class)
            ->filterTable('source', NotificationSourceEnum::SYSTEM->value)
            ->assertCanSeeTableRecords([$system])
            ->assertCanNotSeeTableRecords([$admin]);
    }

    public function test_delete_log_letter(): void
    {
        $log = LogLetter::factory()->create();

        Livewire::test(ManageLogLetters::class)
            ->callAction(TestAction::make(DeleteAction::getDefaultName())->table($log))
            ->assertSuccessful();

        $this->assertModelMissing($log);
    }
}
