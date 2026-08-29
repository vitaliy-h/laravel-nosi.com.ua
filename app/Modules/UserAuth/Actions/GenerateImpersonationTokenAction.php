<?php

namespace App\Modules\UserAuth\Actions;

use App\Modules\User\Models\User;
use Illuminate\Support\Facades\URL;

class GenerateImpersonationTokenAction
{
    public function handle(User $user): string
    {
        return URL::temporarySignedRoute(
            'impersonate',
            now()->addHour(),
            ['user' => $user->id]
        );
    }
}
