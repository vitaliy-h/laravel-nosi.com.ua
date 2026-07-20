<?php

namespace App\Modules\Auth\Actions;

use App\Modules\Auth\Dtos\RegisterData;
use App\Modules\Core\Models\User;
use App\Modules\Notification\Dtos\SendLetterTemplateData;
use App\Modules\Notification\Enums\LetterEnum;
use App\Modules\Notification\Facades\LetterTemplateFacade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Request;

class RegisterAction
{
    public function handle(RegisterData $dto): User
    {
        $user = DB::transaction(function () use ($dto) {
            return User::create([
                'name' => $dto->name,
                'surname' => $dto->surname,
                'phone' => $dto->phone,
                'email' => $dto->email,
                'password' => $dto->password ? Hash::make($dto->password) : null,
                'email_verified_at' => $dto->emailVerifiedAt,
                'ip' => Request::ip(),
            ]);
        });

        if ($dto->email && $dto->password) {
            LetterTemplateFacade::send(
                new SendLetterTemplateData(
                    key: LetterEnum::REGISTRATION,
                    replace: [
                        'user_name' => $dto->name,
                        'user_email' => $dto->email,
                        'user_password' => $dto->password,
                        'login_url' => save_route('auth.index'),
                    ],
                    to: $dto->email,
                )
            );
        }

        return $user;
    }
}
