<?php

namespace App\Modules\UserAuth\Actions;

use App\Modules\UserAuth\Dtos\PasswordRecoveryData;
use App\Modules\User\Queries\FindUserByEmailQuery;
use App\Modules\Notification\Dtos\SendLetterTemplateData;
use App\Modules\Notification\Enums\LetterEnum;
use App\Modules\Notification\Facades\LetterTemplateFacade;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class SendPasswordResetAction
{
    public function __construct(
        private readonly FindUserByEmailQuery $findUserByEmail,
    ) {}

    public function handle(
        PasswordRecoveryData $data
    ): void {

        $user = $this->findUserByEmail
            ->handle($data->email);

        if (! $user) {
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

        LetterTemplateFacade::send(
            new SendLetterTemplateData(
                key: LetterEnum::PASSWORD_RESET,
                replace: [
                    'reset_url' => $resetUrl,
                ],
                to: $user->email
            )
        );
    }
}
