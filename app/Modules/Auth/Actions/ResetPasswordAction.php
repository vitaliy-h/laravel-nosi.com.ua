<?php

namespace App\Modules\Auth\Actions;

use App\Modules\Auth\Dtos\PasswordResetData;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ResetPasswordAction
{
    public function handle(
        PasswordResetData $data
    ): void
    {

        $status = Password::reset(
            [
                'email' => $data->email,
                'password' => $data->password,
                'password_confirmation' => $data->password_confirmation,
                'token' => $data->token,
            ],
            function ($user) use ($data) {

                $user->forceFill([
                    'password' => Hash::make($data->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {

            throw ValidationException::withMessages([
                'email' => [t('auth.password_reset_error')],
            ]);
        }
    }
}
