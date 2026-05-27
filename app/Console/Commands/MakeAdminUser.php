<?php

namespace App\Console\Commands;

use App\Modules\Core\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;

class MakeAdminUser extends Command
{
    protected $signature = 'make:admin-user';

    protected $description = 'Create admin user with admin permission';

    public function handle(): int
    {
        $name = $this->ask('Name', 'Admin');
        $email = $this->ask('Email');
        $password = $this->secret('Password');

        $permission = Permission::firstOrCreate(['name' => 'admin']);

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => $password,
            ]
        );

        $user->givePermissionTo($permission);

        $this->info('Admin user created successfully.');

        return self::SUCCESS;
    }
}
