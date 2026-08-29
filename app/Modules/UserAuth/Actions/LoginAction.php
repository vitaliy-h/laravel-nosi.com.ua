<?php

namespace App\Modules\UserAuth\Actions;

use App\Modules\UserAuth\Dtos\LoginData;
use App\Modules\User\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Request;
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

        $user->update(['ip' => Request::ip()]);

        return $user;
    }
}
