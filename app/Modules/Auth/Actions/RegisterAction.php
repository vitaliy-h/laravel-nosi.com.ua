<?php

namespace App\Modules\Auth\Actions;

use App\Modules\Auth\Dtos\RegisterData;
use App\Modules\Core\Models\User;

class RegisterAction
{
    public function handle(RegisterData $dto): User
    {
        return User::create([
            'name' => $dto->name,
            'email' => $dto->email,
            'password' => bcrypt($dto->password),
        ]);
    }
}
