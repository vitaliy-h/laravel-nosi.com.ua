<?php

namespace App\Modules\Auth\Actions;

use App\Modules\Auth\Dtos\PasswordRecoveryData;
use App\Modules\Notification\Dtos\SendLetterTemplateData;
use App\Modules\Notification\Enums\LetterEnum;
use App\Modules\Core\Enums\PageModule;
use App\Modules\Notification\Facades\LetterTemplateFacade;
use App\Modules\Core\Facades\UrlFacade;
use App\Modules\Core\Repositories\UsersRepository;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class SendPasswordResetAction
{
    public function __construct(
        private readonly UsersRepository $usersRepository,
    )
    {
    }

    public function handle(
        PasswordRecoveryData $data
    ): void
    {

        $user = $this->usersRepository
            ->getByEmail($data->email);

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => [t('auth.user_not_found')],
            ]);
        }

        $token = Password::broker()
            ->createToken($user);

        $resetUrl = UrlFacade::getModuleUrl(
            PageModule::AUTH,
            [],
            [
                'password_reset' => null,
                'token' => $token,
                'email' => $user->email,
            ]
        );

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
