<?php

namespace App\Modules\Auth\Actions;

use App\Modules\Auth\Dtos\RegisterData;
use App\Modules\Core\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Request;

class RegisterAction
{
    public function handle(RegisterData $dto): User
    {
        return DB::transaction(function () use ($dto) {
            return User::create([
                'name'              => $dto->name,
                'email'             => $dto->email,
                'password'          => $dto->password ? Hash::make($dto->password) : null,
                'email_verified_at' => $dto->emailVerifiedAt,
                'ip'                => Request::ip(),
            ]);
        });
    }
}
