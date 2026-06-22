<?php

namespace App\Modules\Auth\Actions;

use App\Modules\Core\Models\User;
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
