<?php

namespace App\Modules\UserAuth\Actions;

use App\Modules\User\Models\User;
use Illuminate\Validation\ValidationException;

class DisconnectSocialProfileAction
{
    public function handle(User $user, string $provider): void
    {
        $profile = $user->socialProfiles()->where('provider', $provider)->firstOrFail();

        $hasPassword = !empty($user->password);
        $hasEmail = !empty($user->email);
        $otherProviders = $user->socialProfiles()
            ->where('provider', '!=', $provider)
            ->exists();

        if (!$hasPassword && !$hasEmail && !$otherProviders) {
            throw ValidationException::withMessages([
                'provider' => [__('site.social.cannot_disconnect_last')],
            ]);
        }

        $profile->delete();
    }
}
