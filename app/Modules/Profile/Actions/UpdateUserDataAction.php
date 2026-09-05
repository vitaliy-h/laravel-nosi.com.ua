<?php

namespace App\Modules\Profile\Actions;

use App\Modules\Profile\Dtos\ProfileUpdateUserDataData;
use App\Modules\User\Models\User;

class UpdateUserDataAction
{
    public function handle(User $user, ProfileUpdateUserDataData $dto): void
    {
        $user->update($dto->toArray());
    }
}
