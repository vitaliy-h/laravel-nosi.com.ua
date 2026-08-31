<?php

namespace App\Modules\UserAuth\Actions;

use App\Modules\Core\Enums\AuthLogActionEnum;
use App\Modules\Core\Services\AuthLogService;
use App\Modules\User\Queries\FindUserByEmailQuery;
use App\Modules\UserAuth\Dtos\PasswordResetData;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ResetPasswordAction
{
    public function __construct(
        private readonly AuthLogService $authLog,
        private readonly FindUserByEmailQuery $findUserByEmail,
    ) {}

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
            function ($user, $password) {

                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        $user = $this->findUserByEmail->handle($data->email);

        if ($status !== Password::PASSWORD_RESET) {

            $this->authLog->failure(
                AuthLogActionEnum::PASSWORD_CHANGE,
                $data->email,
                $user,
            );

            throw ValidationException::withMessages([
                'email' => [t('auth.password_reset_error')],
            ]);
        }

        $this->authLog->success(
            AuthLogActionEnum::PASSWORD_CHANGE,
            $data->email,
            $user,
        );
    }
}
