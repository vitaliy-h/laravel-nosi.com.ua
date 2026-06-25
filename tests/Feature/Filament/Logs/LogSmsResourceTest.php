<?php

namespace Tests\Feature\Filament\Logs;

use App\Filament\Resources\LogSms\Pages\ManageLogSms;
use App\Modules\Core\Models\User;
use App\Modules\Notification\Enums\LogSmsStatusEnum;
use App\Modules\Notification\Enums\NotificationSourceEnum;
use App\Modules\Notification\Models\LogSms;
use Filament\Actions\DeleteAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LogSmsResourceTest extends TestCase
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
        LogSms::factory()->count(3)->create();

        Livewire::test(ManageLogSms::class)
            ->assertSuccessful();
    }

    public function test_list_can_search_by_receiver(): void
    {
        $target = LogSms::factory()->create(['receiver' => '+380991234567']);
        $other  = LogSms::factory()->create(['receiver' => '+380997654321']);

        Livewire::test(ManageLogSms::class)
            ->searchTable('+380991234567')
            ->assertCanSeeTableRecords([$target])
            ->assertCanNotSeeTableRecords([$other]);
    }

    public function test_list_can_search_by_body(): void
    {
        $target = LogSms::factory()->create(['body' => 'Your unique code 99999']);
        $other  = LogSms::factory()->create(['body' => 'Other message']);

        Livewire::test(ManageLogSms::class)
            ->searchTable('unique code 99999')
            ->assertCanSeeTableRecords([$target])
            ->assertCanNotSeeTableRecords([$other]);
    }

    public function test_list_can_filter_by_status(): void
    {
        $sent   = LogSms::factory()->create(['status' => LogSmsStatusEnum::SENT]);
        $failed = LogSms::factory()->failed()->create();

        Livewire::test(ManageLogSms::class)
            ->filterTable('status', LogSmsStatusEnum::SENT->value)
            ->assertCanSeeTableRecords([$sent])
            ->assertCanNotSeeTableRecords([$failed]);
    }

    public function test_list_can_filter_by_source(): void
    {
        $system = LogSms::factory()->create(['source' => NotificationSourceEnum::SYSTEM]);
        $admin  = LogSms::factory()->create(['source' => NotificationSourceEnum::ADMIN]);

        Livewire::test(ManageLogSms::class)
            ->filterTable('source', NotificationSourceEnum::SYSTEM->value)
            ->assertCanSeeTableRecords([$system])
            ->assertCanNotSeeTableRecords([$admin]);
    }

    public function test_delete_log_sms(): void
    {
        $log = LogSms::factory()->create();

        Livewire::test(ManageLogSms::class)
            ->callAction(TestAction::make(DeleteAction::getDefaultName())->table($log))
            ->assertSuccessful();

        $this->assertModelMissing($log);
    }
}
