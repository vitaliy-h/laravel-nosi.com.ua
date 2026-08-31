<?php

namespace App\Modules\Profile\Actions;

use App\Modules\Core\Enums\AuthLogActionEnum;
use App\Modules\Core\Services\AuthLogService;
use App\Modules\User\Models\User;
use Illuminate\Support\Facades\Hash;

class UpdateUserPasswordAction
{
    public function __construct(
        private readonly AuthLogService $authLog,
    ) {}

    public function handle(User $user, string $password): void
    {
        $user->update([
            'password' => Hash::make($password),
        ]);

        $this->authLog->success(
            AuthLogActionEnum::PASSWORD_CHANGE,
            $user->email,
            $user,
        );
    }
}
