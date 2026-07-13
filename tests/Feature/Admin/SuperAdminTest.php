<?php

namespace Tests\Feature\Admin;

use App\Filament\Pages\DebugTools\DebugTools;
use App\Modules\Core\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use Tests\TestCase;

class SuperAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_access_admin_dashboard(): void
    {
        $superAdmin = $this->actingAsSuperAdmin();

        $this->actingAs($superAdmin)
            ->get('/admin')
            ->assertOk();
    }

    public function test_super_admin_can_access_debug_tools_page(): void
    {
        $superAdmin = $this->actingAsSuperAdmin();

        $this->actingAs($superAdmin)
            ->get('/admin/debug-tools')
            ->assertOk();
    }

    public function test_regular_admin_cannot_access_debug_tools_page(): void
    {
        $admin = $this->actingAsAdmin();

        $this->actingAs($admin)
            ->get('/admin/debug-tools')
            ->assertForbidden();
    }

    public function test_regular_user_cannot_access_debug_tools_page(): void
    {
        $this->seedRolesAndPermissions();

        $user = User::factory()->create();
        $user->givePermissionTo('user');

        $this->actingAs($user)
            ->get('/admin/debug-tools')
            ->assertForbidden();
    }

    public function test_guest_cannot_access_debug_tools_page(): void
    {
        $this->get('/admin/debug-tools')
            ->assertRedirect('/admin/login');
    }

    public function test_super_admin_bypasses_gate_for_any_ability(): void
    {
        $superAdmin = $this->actingAsSuperAdmin();

        $this->assertTrue(
            Gate::forUser($superAdmin)->allows('some-made-up-ability-that-does-not-exist')
        );
    }

    public function test_regular_admin_does_not_bypass_gate_for_unknown_ability(): void
    {
        $admin = $this->actingAsAdmin();

        $this->assertFalse(
            Gate::forUser($admin)->allows('some-made-up-ability-that-does-not-exist')
        );
    }

    public function test_super_admin_can_run_whitelisted_artisan_command(): void
    {
        $superAdmin = $this->actingAsSuperAdmin();
        $this->actingAs($superAdmin);

        $component = Livewire::test(DebugTools::class)
            ->call('runCommand', 'cache:clear');

        $this->assertSame('cache:clear', $component->instance()->lastCommand);
        $this->assertNotEmpty($component->instance()->output);
    }

    public function test_super_admin_cannot_run_non_whitelisted_command(): void
    {
        $superAdmin = $this->actingAsSuperAdmin();
        $this->actingAs($superAdmin);

        Livewire::test(DebugTools::class)
            ->call('runCommand', 'env')
            ->assertStatus(422);
    }

    public function test_regular_admin_cannot_mount_debug_tools_component(): void
    {
        $admin = $this->actingAsAdmin();
        $this->actingAs($admin);

        Livewire::test(DebugTools::class)
            ->assertStatus(403);
    }
}
