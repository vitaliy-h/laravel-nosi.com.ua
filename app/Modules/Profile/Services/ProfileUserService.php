<?php

namespace App\Modules\Profile\Services;

use App\Modules\Core\Models\User;
use App\Modules\Profile\Dtos\ProfileUpdateUserDataDto;
use Illuminate\Support\Facades\Hash;

class ProfileUserService
{
    public function updateUserData(User $user, ProfileUpdateUserDataDto $dataDto): bool
    {
        try {
            return $user->update($dataDto->toArray());
        } catch (\Throwable $t) {
            report($t);
        }
        return false;
    }

    public function updateUserPassword(User $user, string $password): bool
    {
        try {
            return $user->update(['password' => Hash::make($password),]);
        } catch (\Throwable $t) {
            report($t);
        }
        return false;
    }

    public function deleteUserAccount(User $user): bool
    {
        try {
            return $user->delete();
        } catch (\Throwable $t) {
            report($t);
        }
        return false;
    }
}
