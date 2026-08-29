<?php

namespace App\Modules\Profile\Actions;

use App\Modules\User\Models\User;

class DeleteUserAccountAction
{
    public function handle(User $user): void
    {
        $user->delete();
    }
}
