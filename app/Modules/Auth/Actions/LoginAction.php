<?php

namespace App\Modules\Auth\Actions;

use App\Modules\Auth\Dtos\LoginData;
use App\Modules\Core\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginAction
{
    public function handle(LoginData $dto): User
    {
        $user = User::where('email', $dto->email)->first();

        if (!$user || !Hash::check($dto->password, $user->password)) {

            throw ValidationException::withMessages([
                'email' => t('auth.invalid_credentials'),
            ]);
        }

        return $user;
    }
}
