<?php

namespace App\Modules\UserAuth\Actions;

use App\Modules\Notification\Dtos\SendLetterTemplateData;
use App\Modules\Notification\Enums\LetterEnum;
use App\Modules\Notification\Services\LetterTemplateService;
use App\Modules\User\Models\User;
use App\Modules\UserAuth\Dtos\RegisterData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Request;

class RegisterAction
{
    public function __construct(
        private readonly LetterTemplateService $letters,
    ) {}

    public function handle(RegisterData $dto): User
    {
        $user = DB::transaction(function () use ($dto) {
            return User::create([
                'name'              => $dto->name,
                'surname'           => $dto->surname,
                'phone'             => $dto->phone,
                'email'             => $dto->email,
                'password'          => $dto->password ? Hash::make($dto->password) : null,
                'email_verified_at' => $dto->emailVerifiedAt,
                'ip'                => Request::ip(),
            ]);
        });

        if ($dto->email && $dto->password) {
            DB::afterCommit(fn () => $this->letters->send(
                new SendLetterTemplateData(
                    key: LetterEnum::REGISTRATION,
                    replace: [
                        'user_name'     => $dto->name,
                        'user_email'    => $dto->email,
                        'user_password' => $dto->password,
                        'login_url'     => save_route('auth.index'),
                    ],
                    to: $dto->email,
                )
            ));
        }

        return $user;
    }
}
