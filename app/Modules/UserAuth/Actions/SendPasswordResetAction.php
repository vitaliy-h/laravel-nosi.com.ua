<?php

namespace App\Modules\UserAuth\Actions;

use App\Modules\Core\Enums\AuthLogActionEnum;
use App\Modules\Core\Services\AuthLogService;
use App\Modules\Notification\Dtos\SendLetterTemplateData;
use App\Modules\Notification\Enums\LetterEnum;
use App\Modules\Notification\Services\LetterTemplateService;
use App\Modules\User\Queries\FindUserByEmailQuery;
use App\Modules\UserAuth\Dtos\PasswordRecoveryData;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class SendPasswordResetAction
{
    public function __construct(
        private readonly FindUserByEmailQuery $findUserByEmail,
        private readonly AuthLogService $authLog,
        private readonly LetterTemplateService $letters,
    ) {}

    public function handle(
        PasswordRecoveryData $data
    ): void {

        $user = $this->findUserByEmail
            ->handle($data->email);

        if (!$user) {

            $this->authLog->failure(
                AuthLogActionEnum::PASSWORD_RESET_REQUEST,
                $data->email,
            );

            throw ValidationException::withMessages([
                'email' => [t('auth.user_not_found')],
            ]);
        }

        $token = Password::broker()
            ->createToken($user);

        $resetUrl = save_route('auth.index', [
            'token' => $token,
            'email' => $user->email,
        ]);
        $resetUrl = str_replace('?', '?password_reset&', $resetUrl);

        $this->letters->send(
            new SendLetterTemplateData(
                key: LetterEnum::PASSWORD_RESET,
                replace: [
                    'reset_url' => $resetUrl,
                ],
                to: $user->email
            )
        );

        $this->authLog->success(
            AuthLogActionEnum::PASSWORD_RESET_REQUEST,
            $user->email,
            $user,
        );
    }
}
