<?php

namespace App\Modules\User\Queries;

use App\Modules\User\Models\User;

class FindUserByEmailQuery
{
    public function handle(string $email): ?User
    {
        return User::query()
            ->where('email', $email)
            ->first();
    }
}
