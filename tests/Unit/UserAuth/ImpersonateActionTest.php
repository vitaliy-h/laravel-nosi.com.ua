<?php

namespace Tests\Unit\UserAuth;

use App\Modules\User\Models\User;
use App\Modules\UserAuth\Actions\ImpersonateAction;
use App\Modules\UserAuth\Actions\LeaveImpersonationAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

/**
 * Both Actions take a Session rather than a Request, so they run outside HTTP.
 */
class ImpersonateActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_impersonate_stores_the_current_user_and_switches_login(): void
    {
        $admin = User::factory()->create();
        $target = User::factory()->create();

        Auth::login($admin);
        $session = session()->driver();

        app(ImpersonateAction::class)->handle($session, $target);

        $this->assertSame($target->id, Auth::id());
        $this->assertSame($admin->id, $session->get(ImpersonateAction::SESSION_KEY));
    }

    public function test_leaving_restores_the_stored_user(): void
    {
        $admin = User::factory()->create();
        $target = User::factory()->create();

        Auth::login($target);
        $session = session()->driver();
        $session->put(ImpersonateAction::SESSION_KEY, $admin->id);

        app(LeaveImpersonationAction::class)->handle($session);

        $this->assertSame($admin->id, Auth::id());
        $this->assertFalse($session->has(ImpersonateAction::SESSION_KEY));
    }

    public function test_leaving_logs_out_when_the_stored_user_is_gone(): void
    {
        $target = User::factory()->create();

        Auth::login($target);
        $session = session()->driver();
        $session->put(ImpersonateAction::SESSION_KEY, 999999);

        app(LeaveImpersonationAction::class)->handle($session);

        $this->assertNull(Auth::id());
    }
}
