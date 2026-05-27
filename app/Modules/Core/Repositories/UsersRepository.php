<?php

namespace App\Modules\Core\Repositories;

use App\Modules\Core\Models\User;

class UsersRepository
{
    public function getByEmail($email)
    {
        return User::query()
            ->where('email', $email)
            ->first();
    }
}
