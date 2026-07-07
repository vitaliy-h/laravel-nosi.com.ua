<?php

namespace App\Modules\Core\Queries;

use App\Modules\Core\Models\User;

class FindUserByEmailQuery
{
    public function handle(string $email): ?User
    {
        return User::query()
            ->where('email', $email)
            ->first();
    }
}
