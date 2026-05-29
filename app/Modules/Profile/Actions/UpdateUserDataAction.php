<?php

namespace App\Modules\Profile\Actions;

use App\Modules\Core\Models\User;
use App\Modules\Profile\Dtos\ProfileUpdateUserDataData;

class UpdateUserDataAction
{
    public function handle(User $user, ProfileUpdateUserDataData $dto): void
    {
        $user->update($dto->toArray());
    }
}
