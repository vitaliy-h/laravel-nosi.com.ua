<?php

namespace App\Modules\Auth\Actions;

use App\Modules\Core\Models\SocialProfile;
use App\Modules\Core\Models\User;
use Laravel\Socialite\Contracts\User as SocialUser;

class ConnectSocialProfileAction
{
    public function handle(User $user, string $provider, SocialUser $socialUser): SocialProfile
    {
        return SocialProfile::updateOrCreate(
            [
                'provider'    => $provider,
                'provider_id' => $socialUser->getId(),
            ],
            [
                'user_id'          => $user->id,
                'token'            => $socialUser->token,
                'refresh_token'    => $socialUser->refreshToken,
                'token_expires_at' => $socialUser->expiresIn
                    ? now()->addSeconds($socialUser->expiresIn)
                    : null,
                'avatar'           => $socialUser->getAvatar(),
            ]
        );
    }
}
