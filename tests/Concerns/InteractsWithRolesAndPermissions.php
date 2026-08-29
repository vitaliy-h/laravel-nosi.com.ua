<?php

namespace Tests\Concerns;

use App\Modules\User\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

trait InteractsWithRolesAndPermissions
{
    /**
     * Create the base set of permissions used across the application.
     *
     * Mirrors the permissions block of RolesAndPermissionsSeeder without
     * creating roles or users.
     */
    protected function seedRolesAndPermissions(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'admin',
            'user',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }
    }

    /**
     * Create a user that holds the "admin" permission.
     *
     * Does not call actingAs(); the caller decides how to authenticate.
     */
    protected function actingAsAdmin(): User
    {
        $this->seedRolesAndPermissions();

        $user = User::factory()->create();

        $user->givePermissionTo('admin');

        return $user;
    }

    /**
     * Create a user that holds the "super_admin" role.
     *
     * Does not call actingAs(); the caller decides how to authenticate.
     */
    protected function actingAsSuperAdmin(): User
    {
        $this->seedRolesAndPermissions();

        $role = Role::firstOrCreate([
            'name' => 'super_admin',
            'guard_name' => 'web',
        ]);

        $user = User::factory()->create();

        $user->assignRole($role);

        return $user;
    }
}
