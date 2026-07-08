<?php

namespace Tests\Feature\Admin;

use App\Filament\Pages\Auth\Login;
use App\Modules\Core\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_login_page_is_accessible(): void
    {
        $this->get('/admin/login')->assertOk();
    }

    public function test_admin_can_log_in_via_livewire_form(): void
    {
        $this->seedRolesAndPermissions();

        $admin = User::factory()->create();
        $admin->givePermissionTo('admin');

        Livewire::test(Login::class)
            ->fillForm([
                'login' => $admin->email,
                'password' => 'password',
            ])
            ->call('authenticate')
            ->assertHasNoFormErrors();

        $this->assertAuthenticatedAs($admin);
    }

    public function test_non_admin_cannot_access_admin_panel(): void
    {
        $this->seedRolesAndPermissions();

        $regularUser = User::factory()->create();
        $regularUser->givePermissionTo('user');

        $response = $this->actingAs($regularUser)->get('/admin');

        $response->assertForbidden();
    }

    public function test_admin_can_access_admin_dashboard(): void
    {
        $admin = $this->actingAsAdmin();

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk();
    }

    public function test_login_with_wrong_password_shows_form_error(): void
    {
        $this->seedRolesAndPermissions();

        $admin = User::factory()->create();
        $admin->givePermissionTo('admin');

        Livewire::test(Login::class)
            ->fillForm([
                'login' => $admin->email,
                'password' => 'wrong-password',
            ])
            ->call('authenticate')
            ->assertHasFormErrors(['login']);

        $this->assertGuest();
    }
}
