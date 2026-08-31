<?php

namespace App\Modules\UserAuth\Actions;

use App\Modules\Core\Enums\AuthLogActionEnum;
use App\Modules\Core\Exceptions\AuthCooldownException;
use App\Modules\Core\Services\AuthCooldownService;
use App\Modules\Core\Services\AuthLogService;
use App\Modules\UserAuth\Dtos\LoginData;
use App\Modules\User\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Request;
use Illuminate\Validation\ValidationException;

class LoginAction
{
    public function __construct(
        private readonly AuthLogService $authLog,
        private readonly AuthCooldownService $cooldown,
    ) {}

    public function handle(LoginData $dto): User
    {
        try {
            $this->cooldown->assertNotBlocked($dto->email);
        } catch (AuthCooldownException $exception) {
            $this->authLog->blocked(AuthLogActionEnum::PASSWORD, $dto->email);

            throw $exception->asValidationException('email');
        }

        $user = User::where('email', $dto->email)->first();

        if (!$user || !Hash::check($dto->password, $user->password)) {

            $this->cooldown->hit($dto->email);

            $this->authLog->failure(AuthLogActionEnum::PASSWORD, $dto->email, $user);

            throw ValidationException::withMessages([
                'email' => t('auth.invalid_credentials'),
            ]);
        }

        $this->cooldown->clear($dto->email);

        $user->update(['ip' => Request::ip()]);

        $this->authLog->success(AuthLogActionEnum::PASSWORD, $dto->email, $user);

        return $user;
    }
}
