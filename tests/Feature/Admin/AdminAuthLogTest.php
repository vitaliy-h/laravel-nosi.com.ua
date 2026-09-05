<?php

namespace Tests\Feature\Admin;

use App\Filament\Pages\Auth\Login;
use App\Modules\Core\Enums\AuthLogActionEnum;
use App\Modules\Core\Enums\AuthLogResultEnum;
use App\Modules\Core\Enums\AuthLogSourceEnum;
use App\Modules\Core\Models\AuthLog;
use App\Modules\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminAuthLogTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        config(['auth_log.cooldown_seconds' => 5]);

        $this->seedRolesAndPermissions();

        $this->admin = User::factory()->create();
        $this->admin->givePermissionTo('admin');
    }

    public function test_successful_admin_login_is_logged_as_admin_panel_source(): void
    {
        Livewire::test(Login::class)
            ->fillForm([
                'login'    => $this->admin->email,
                'password' => 'password',
            ])
            ->call('authenticate');

        $log = AuthLog::query()->firstOrFail();

        $this->assertSame($this->admin->id, $log->user_id);
        $this->assertSame(AuthLogSourceEnum::ADMIN_PANEL, $log->source);
        $this->assertSame(AuthLogActionEnum::PASSWORD, $log->action);
        $this->assertSame(AuthLogResultEnum::SUCCESS, $log->result);
    }

    public function test_failed_admin_login_is_logged(): void
    {
        Livewire::test(Login::class)
            ->fillForm([
                'login'    => $this->admin->email,
                'password' => 'wrong-password',
            ])
            ->call('authenticate');

        $log = AuthLog::query()->firstOrFail();

        $this->assertSame(AuthLogResultEnum::FAILURE, $log->result);
        $this->assertSame($this->admin->id, $log->user_id);
    }

    public function test_admin_login_within_the_cooldown_is_blocked_and_logged(): void
    {
        Livewire::test(Login::class)
            ->fillForm([
                'login'    => $this->admin->email,
                'password' => 'wrong-password',
            ])
            ->call('authenticate');

        Livewire::test(Login::class)
            ->fillForm([
                'login'    => $this->admin->email,
                'password' => 'password',
            ])
            ->call('authenticate')
            ->assertHasFormErrors(['login']);

        $this->assertGuest();

        $this->assertSame(
            1,
            AuthLog::query()->where('result', AuthLogResultEnum::BLOCKED)->count(),
        );
    }

    public function test_unknown_admin_login_is_logged_without_a_user(): void
    {
        Livewire::test(Login::class)
            ->fillForm([
                'login'    => 'ghost@example.com',
                'password' => 'password',
            ])
            ->call('authenticate');

        $log = AuthLog::query()->firstOrFail();

        $this->assertNull($log->user_id);
        $this->assertSame('ghost@example.com', $log->login);
        $this->assertSame(AuthLogResultEnum::FAILURE, $log->result);
    }
}
