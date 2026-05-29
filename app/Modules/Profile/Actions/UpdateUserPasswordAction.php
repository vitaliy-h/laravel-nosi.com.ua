<?php

namespace App\Modules\Profile\Actions;

use App\Modules\Core\Models\User;
use Illuminate\Support\Facades\Hash;

class UpdateUserPasswordAction
{
    public function handle(User $user, string $password): void
    {
        $user->update([
            'password' => Hash::make($password),
        ]);
    }
}
