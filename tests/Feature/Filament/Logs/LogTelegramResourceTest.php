<?php

namespace Tests\Feature\Filament\Logs;

use App\Filament\Resources\LogTelegram\Pages\ManageLogTelegram;
use App\Modules\Notification\Enums\LogTelegramStatusEnum;
use App\Modules\Notification\Enums\NotificationSourceEnum;
use App\Modules\Notification\Models\LogTelegram;
use App\Modules\User\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LogTelegramResourceTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = $this->actingAsAdmin();
        $this->actingAs($this->admin);
    }

    public function test_list_renders_and_finds_by_receiver(): void
    {
        $target = $this->log(['receiver' => '6572793']);
        $other = $this->log(['receiver' => '111']);

        Livewire::test(ManageLogTelegram::class)
            ->assertSuccessful()
            ->searchTable('6572793')
            ->assertCanSeeTableRecords([$target])
            ->assertCanNotSeeTableRecords([$other]);
    }

    public function test_list_can_filter_by_status(): void
    {
        $sent = $this->log(['status' => LogTelegramStatusEnum::SENT]);
        $failed = $this->log(['status' => LogTelegramStatusEnum::FAILED, 'error_message' => 'chat not found']);

        Livewire::test(ManageLogTelegram::class)
            ->filterTable('status', LogTelegramStatusEnum::FAILED->value)
            ->assertCanSeeTableRecords([$failed])
            ->assertCanNotSeeTableRecords([$sent]);
    }

    public function test_delete_log_telegram(): void
    {
        $log = $this->log();

        Livewire::test(ManageLogTelegram::class)
            ->callAction(TestAction::make(DeleteAction::getDefaultName())->table($log))
            ->assertSuccessful();

        $this->assertModelMissing($log);
    }

    private function log(array $attributes = []): LogTelegram
    {
        return LogTelegram::query()->create($attributes + [
            'receiver' => '123',
            'body'     => '🛒 <b>Нове замовлення №1</b>',
            'status'   => LogTelegramStatusEnum::QUEUED,
            'source'   => NotificationSourceEnum::SYSTEM,
        ]);
    }
}
