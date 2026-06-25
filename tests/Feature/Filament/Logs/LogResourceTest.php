<?php

namespace Tests\Feature\Filament\Logs;

use App\Filament\Resources\Logs\Pages\ManageLogs;
use App\Modules\Core\Enums\LogEventEnum;
use App\Modules\Core\Models\Log;
use App\Modules\Core\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LogResourceTest extends TestCase
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
        Log::factory()->count(3)->create();

        Livewire::test(ManageLogs::class)
            ->assertSuccessful();
    }

    public function test_list_can_search_by_ip(): void
    {
        $target = Log::factory()->create(['ip_address' => '10.0.0.111']);
        $other  = Log::factory()->create(['ip_address' => '10.0.0.222']);

        Livewire::test(ManageLogs::class)
            ->searchTable('10.0.0.111')
            ->assertCanSeeTableRecords([$target])
            ->assertCanNotSeeTableRecords([$other]);
    }

    public function test_list_can_filter_by_model_type(): void
    {
        $userLog = Log::factory()->create(['model_type' => User::class]);
        $otherLog = Log::factory()->create(['model_type' => Log::class]);

        Livewire::test(ManageLogs::class)
            ->filterTable('model_type', User::class)
            ->assertCanSeeTableRecords([$userLog])
            ->assertCanNotSeeTableRecords([$otherLog]);
    }

    public function test_delete_log(): void
    {
        $log = Log::factory()->create();

        Livewire::test(ManageLogs::class)
            ->callAction(TestAction::make(DeleteAction::getDefaultName())->table($log))
            ->assertSuccessful();

        $this->assertModelMissing($log);
    }
}
