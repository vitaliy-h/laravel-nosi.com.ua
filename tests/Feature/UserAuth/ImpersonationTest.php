<?php

namespace Tests\Feature\UserAuth;

use App\Modules\User\Models\User;
use App\Modules\UserAuth\Actions\GenerateImpersonationTokenAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithPages;
use Tests\TestCase;

class ImpersonationTest extends TestCase
{
    use InteractsWithPages;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedPagesForRouting();
    }

    // ─── impersonate ───────────────────────────────────────────────────────────

    public function test_impersonate_without_signature_returns_403(): void
    {
        $user = User::factory()->create();

        $this->get('/user_auth/impersonate/' . $user->id)
            ->assertStatus(403);
    }

    public function test_impersonate_with_valid_signed_url_logs_in_as_target_user(): void
    {
        $admin = $this->actingAsAdmin();
        $target = User::factory()->create();

        $url = app(GenerateImpersonationTokenAction::class)->handle($target);

        $this->actingAs($admin)->get($url);

        $this->assertAuthenticatedAs($target);
    }

    public function test_impersonate_stores_admin_id_in_session(): void
    {
        $admin = $this->actingAsAdmin();
        $target = User::factory()->create();

        $url = app(GenerateImpersonationTokenAction::class)->handle($target);

        $response = $this->actingAs($admin)->get($url);

        $response->assertSessionHas('impersonating_admin_id', $admin->id);
    }

    public function test_impersonate_redirects_to_home(): void
    {
        $admin = $this->actingAsAdmin();
        $target = User::factory()->create();

        $url = app(GenerateImpersonationTokenAction::class)->handle($target);

        $this->actingAs($admin)->get($url)
            ->assertRedirect('/');
    }

    public function test_impersonate_with_tampered_url_returns_403(): void
    {
        $target = User::factory()->create();

        $url = app(GenerateImpersonationTokenAction::class)->handle($target);
        $tampered = $url . 'x';

        $this->get($tampered)->assertStatus(403);
    }

    // ─── leave impersonation ───────────────────────────────────────────────────

    public function test_leave_requires_authentication(): void
    {
        $this->post(route('impersonate.leave'))
            ->assertStatus(302);

        $this->assertGuest();
    }

    public function test_leave_restores_original_admin(): void
    {
        $admin = $this->actingAsAdmin();
        $target = User::factory()->create();

        $this->actingAs($target)
            ->withSession(['impersonating_admin_id' => $admin->id])
            ->post(route('impersonate.leave'));

        $this->assertAuthenticatedAs($admin);
    }

    public function test_leave_clears_impersonating_session_key(): void
    {
        $admin = $this->actingAsAdmin();
        $target = User::factory()->create();

        $response = $this->actingAs($target)
            ->withSession(['impersonating_admin_id' => $admin->id])
            ->post(route('impersonate.leave'));

        $response->assertSessionMissing('impersonating_admin_id');
    }

    public function test_leave_redirects_to_admin_panel(): void
    {
        $admin = $this->actingAsAdmin();
        $target = User::factory()->create();

        $this->actingAs($target)
            ->withSession(['impersonating_admin_id' => $admin->id])
            ->post(route('impersonate.leave'))
            ->assertRedirect(filament()->getPanel('admin')->getUrl());
    }

    public function test_leave_logs_out_if_original_admin_no_longer_exists(): void
    {
        $admin = $this->actingAsAdmin();
        $target = User::factory()->create();
        $adminId = $admin->id;

        $admin->delete();

        $this->actingAs($target)
            ->withSession(['impersonating_admin_id' => $adminId])
            ->post(route('impersonate.leave'));

        $this->assertGuest();
    }

    // ─── session isolation ─────────────────────────────────────────────────────

    public function test_impersonate_url_opened_without_session_has_no_return_key(): void
    {
        $target = User::factory()->create();

        $url = app(GenerateImpersonationTokenAction::class)->handle($target);

        // Simulates opening the link in a different browser (no prior session / admin auth)
        $response = $this->get($url);

        $response->assertSessionMissing('impersonating_admin_id');
    }
}
