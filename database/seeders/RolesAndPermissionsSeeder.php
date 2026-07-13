<?php

namespace Database\Seeders;

use App\Modules\Core\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cache
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        /*
        |--------------------------------------------------------------------------
        | Permissions
        |--------------------------------------------------------------------------
        */

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

        /*
        |--------------------------------------------------------------------------
        | Roles
        |--------------------------------------------------------------------------
        */

        $superAdminRole = Role::firstOrCreate([
            'name' => 'super_admin',
            'guard_name' => 'web',
        ]);

        $adminRole = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);

        $userRole = Role::firstOrCreate([
            'name' => 'user',
            'guard_name' => 'web',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Assign permissions to roles
        |--------------------------------------------------------------------------
        */

        // Super Admin — all permissions (also bypasses all Gate checks, see AppServiceProvider::boot)
        $superAdminRole->syncPermissions(Permission::all());

        // Admin — all permissions
        $adminRole->syncPermissions(Permission::all());

        // User — minimal
        $userRole->syncPermissions([
            'user',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Create Super Admin User
        |--------------------------------------------------------------------------
        */

        $superAdminUser = User::firstOrCreate(
            ['email' => 'ottofonf@gmail.com'],
            [
                'name' => 'Super Admin',
                'password' => '$2y$12$blJnokKfAt3LkK6Bv.uGSuYhI/Ec9CH8rByOP9Majgup6r6r1BspW',
            ]
        );

        if (!$superAdminUser->hasRole('super_admin')) {
            $superAdminUser->assignRole($superAdminRole);
        }

        /*
        |--------------------------------------------------------------------------
        | Create Admin User
        |--------------------------------------------------------------------------
        */

        $adminUser = User::firstOrCreate(
            ['email' => 'admin'],
            [
                'name' => 'Admin',
                'password' => bcrypt('password'),
            ]
        );

        // Assign role
        if (!$adminUser->hasRole('admin')) {
            $adminUser->assignRole($adminRole);
        }

        /*
        |--------------------------------------------------------------------------
        | Create Test User
        |--------------------------------------------------------------------------
        */

        $testUser = User::firstOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
            ]
        );

        if (!$testUser->hasRole('user')) {
            $testUser->assignRole($userRole);
        }
    }
}
