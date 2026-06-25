<?php

namespace Tests\Feature\Filament\Users;

use App\Filament\Resources\Users\Pages\ManageUsers;
use App\Modules\Core\Models\User;
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

    public function test_create_user_requires_email(): void
    {
        Livewire::test(ManageUsers::class)
            ->callAction(TestAction::make(CreateAction::getDefaultName())->table(), data: [
                'name'     => 'John',
                'password' => 'secret123',
            ])
            ->assertHasFormErrors(['email']);
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
}
