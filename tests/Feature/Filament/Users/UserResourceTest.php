<?php

namespace Tests\Feature\Filament\Users;

use App\Filament\Resources\Users\Pages\ManageUsers;
use App\Modules\User\Models\User;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserResourceTest extends TestCase
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
        User::factory()->count(3)->create();

        Livewire::test(ManageUsers::class)
            ->assertSuccessful();
    }

    public function test_list_can_search_by_name(): void
    {
        $target = User::factory()->create(['name' => 'UniqueSearchName']);
        $other = User::factory()->create(['name' => 'AnotherPerson']);

        Livewire::test(ManageUsers::class)
            ->searchTable('UniqueSearchName')
            ->assertCanSeeTableRecords([$target])
            ->assertCanNotSeeTableRecords([$other]);
    }

    public function test_list_can_search_by_email(): void
    {
        $target = User::factory()->create(['email' => 'find@example.com']);
        $other = User::factory()->create(['email' => 'other@example.com']);

        Livewire::test(ManageUsers::class)
            ->searchTable('find@example.com')
            ->assertCanSeeTableRecords([$target])
            ->assertCanNotSeeTableRecords([$other]);
    }

    public function test_list_can_filter_by_role(): void
    {
        $role = Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web']);

        $withRole = User::factory()->create();
        $withRole->assignRole($role);

        $withoutRole = User::factory()->create();

        Livewire::test(ManageUsers::class)
            ->filterTable('roles', $role)
            ->assertCanSeeTableRecords([$withRole])
            ->assertCanNotSeeTableRecords([$withoutRole]);
    }

    public function test_create_user_with_valid_data(): void
    {
        Livewire::test(ManageUsers::class)
            ->callAction(TestAction::make(CreateAction::getDefaultName())->table(), data: [
                'name'     => 'John',
                'email'    => 'john@example.com',
                'password' => 'secret123',
            ])
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('users', ['email' => 'john@example.com', 'name' => 'John']);
    }

    public function test_create_user_requires_name(): void
    {
        Livewire::test(ManageUsers::class)
            ->callAction(TestAction::make(CreateAction::getDefaultName())->table(), data: [
                'email'    => 'john@example.com',
                'password' => 'secret123',
            ])
            ->assertHasFormErrors(['name']);
    }

    public function test_create_user_requires_unique_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        Livewire::test(ManageUsers::class)
            ->callAction(TestAction::make(CreateAction::getDefaultName())->table(), data: [
                'name'     => 'Jane',
                'email'    => 'taken@example.com',
                'password' => 'secret123',
            ])
            ->assertHasFormErrors(['email']);
    }

    public function test_create_user_requires_password(): void
    {
        Livewire::test(ManageUsers::class)
            ->callAction(TestAction::make(CreateAction::getDefaultName())->table(), data: [
                'name'  => 'John',
                'email' => 'john@example.com',
            ])
            ->assertHasFormErrors(['password']);
    }

    public function test_edit_user_with_valid_data(): void
    {
        $user = User::factory()->create(['name' => 'OldName']);

        Livewire::test(ManageUsers::class)
            ->callAction(TestAction::make(EditAction::getDefaultName())->table($user), data: [
                'name'  => 'UpdatedName',
                'email' => $user->email,
            ])
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'UpdatedName']);
    }

    public function test_edit_user_requires_unique_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);
        $user = User::factory()->create(['email' => 'mine@example.com']);

        Livewire::test(ManageUsers::class)
            ->callAction(TestAction::make(EditAction::getDefaultName())->table($user), data: [
                'name'  => $user->name,
                'email' => 'taken@example.com',
            ])
            ->assertHasFormErrors(['email']);
    }

    public function test_delete_user(): void
    {
        $user = User::factory()->create();

        Livewire::test(ManageUsers::class)
            ->callAction(TestAction::make(DeleteAction::getDefaultName())->table($user))
            ->assertSuccessful();

        $this->assertModelMissing($user);
    }

    public function test_regular_admin_cannot_see_super_admin_in_list(): void
    {
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole($superAdminRole);

        $visibleUser = User::factory()->create();

        Livewire::test(ManageUsers::class)
            ->assertCanNotSeeTableRecords([$superAdmin])
            ->assertCanSeeTableRecords([$visibleUser]);
    }

    public function test_regular_admin_cannot_find_super_admin_via_search(): void
    {
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdmin = User::factory()->create(['email' => 'hidden-super-admin@example.com']);
        $superAdmin->assignRole($superAdminRole);

        Livewire::test(ManageUsers::class)
            ->searchTable('hidden-super-admin@example.com')
            ->assertCanNotSeeTableRecords([$superAdmin]);
    }

    public function test_regular_admin_cannot_update_super_admin(): void
    {
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole($superAdminRole);

        $this->assertFalse($this->admin->can('update', $superAdmin));
        $this->assertFalse($this->admin->can('delete', $superAdmin));
    }

    public function test_super_admin_can_see_super_admin_in_list(): void
    {
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole($superAdminRole);

        $this->actingAs($superAdmin);

        Livewire::test(ManageUsers::class)
            ->assertCanSeeTableRecords([$superAdmin]);
    }

    public function test_super_admin_can_update_super_admin(): void
    {
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole($superAdminRole);

        $this->assertTrue($superAdmin->can('update', $superAdmin));
        $this->assertTrue($superAdmin->can('delete', $superAdmin));
    }

    public function test_regular_admin_cannot_smuggle_super_admin_role_via_crafted_edit_request(): void
    {
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $target = User::factory()->create();

        // Simulate a crafted request that includes the super_admin role id even
        // though the UI never exposes it to a regular admin. Filament validates
        // the submitted ids against the (role-scoped) relationship query, so this
        // is rejected as a form error before it ever reaches the database.
        Livewire::test(ManageUsers::class)
            ->callAction(TestAction::make(EditAction::getDefaultName())->table($target), data: [
                'name'  => $target->name,
                'email' => $target->email,
                'roles' => [$superAdminRole->id],
            ])
            ->assertHasFormErrors(['roles.0']);

        $this->assertFalse($target->fresh()->hasRole('super_admin'));
    }

    public function test_super_admin_can_assign_super_admin_role(): void
    {
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $actingSuperAdmin = User::factory()->create();
        $actingSuperAdmin->assignRole($superAdminRole);
        $this->actingAs($actingSuperAdmin);

        $target = User::factory()->create();

        Livewire::test(ManageUsers::class)
            ->callAction(TestAction::make(EditAction::getDefaultName())->table($target), data: [
                'name'  => $target->name,
                'email' => $target->email,
                'roles' => [$superAdminRole->id],
            ])
            ->assertHasNoFormErrors();

        $this->assertTrue($target->fresh()->hasRole('super_admin'));
    }
}
