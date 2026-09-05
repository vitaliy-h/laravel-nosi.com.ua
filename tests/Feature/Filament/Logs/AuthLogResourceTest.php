<?php

namespace Tests\Feature\Filament\Logs;

use App\Filament\Resources\AuthLogs\Pages\ManageAuthLogs;
use App\Modules\Core\Enums\AuthLogActionEnum;
use App\Modules\Core\Enums\AuthLogResultEnum;
use App\Modules\Core\Enums\AuthLogSourceEnum;
use App\Modules\Core\Models\AuthLog;
use App\Modules\User\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AuthLogResourceTest extends TestCase
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
        AuthLog::factory()->count(3)->create();

        Livewire::test(ManageAuthLogs::class)
            ->assertSuccessful();
    }

    public function test_list_can_search_by_login(): void
    {
        $target = AuthLog::factory()->create(['login' => 'target@example.com']);
        $other = AuthLog::factory()->create(['login' => 'other@example.com']);

        Livewire::test(ManageAuthLogs::class)
            ->searchTable('target@example.com')
            ->assertCanSeeTableRecords([$target])
            ->assertCanNotSeeTableRecords([$other]);
    }

    public function test_list_can_search_by_ip(): void
    {
        $target = AuthLog::factory()->create(['ip_address' => '10.0.0.111']);
        $other = AuthLog::factory()->create(['ip_address' => '10.0.0.222']);

        Livewire::test(ManageAuthLogs::class)
            ->searchTable('10.0.0.111')
            ->assertCanSeeTableRecords([$target])
            ->assertCanNotSeeTableRecords([$other]);
    }

    public function test_list_can_filter_by_result(): void
    {
        $blocked = AuthLog::factory()->forResult(AuthLogResultEnum::BLOCKED)->create();
        $success = AuthLog::factory()->forResult(AuthLogResultEnum::SUCCESS)->create();

        Livewire::test(ManageAuthLogs::class)
            ->filterTable('result', AuthLogResultEnum::BLOCKED->value)
            ->assertCanSeeTableRecords([$blocked])
            ->assertCanNotSeeTableRecords([$success]);
    }

    public function test_list_can_filter_by_action(): void
    {
        $social = AuthLog::factory()->forAction(AuthLogActionEnum::SOCIAL)->create();
        $password = AuthLog::factory()->forAction(AuthLogActionEnum::PASSWORD)->create();

        Livewire::test(ManageAuthLogs::class)
            ->filterTable('action', AuthLogActionEnum::SOCIAL->value)
            ->assertCanSeeTableRecords([$social])
            ->assertCanNotSeeTableRecords([$password]);
    }

    public function test_list_can_filter_by_source(): void
    {
        $admin = AuthLog::factory()->create(['source' => AuthLogSourceEnum::ADMIN_PANEL]);
        $client = AuthLog::factory()->create(['source' => AuthLogSourceEnum::WEBSITE]);

        Livewire::test(ManageAuthLogs::class)
            ->filterTable('source', AuthLogSourceEnum::ADMIN_PANEL->value)
            ->assertCanSeeTableRecords([$admin])
            ->assertCanNotSeeTableRecords([$client]);
    }

    public function test_delete_auth_log(): void
    {
        $log = AuthLog::factory()->create();

        Livewire::test(ManageAuthLogs::class)
            ->callAction(TestAction::make(DeleteAction::getDefaultName())->table($log))
            ->assertSuccessful();

        $this->assertModelMissing($log);
    }

    public function test_clear_action_wipes_the_journal(): void
    {
        AuthLog::factory()->count(4)->create();

        Livewire::test(ManageAuthLogs::class)
            ->callAction('clear_log')
            ->assertSuccessful();

        $this->assertSame(0, AuthLog::query()->count());
    }
}
