<?php

namespace App\Modules\UserAuth\Actions;

use App\Modules\User\Models\User;
use App\Modules\UserAuth\Models\SocialProfile;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Contracts\User as SocialUser;

class ConnectSocialProfileAction
{
    public function handle(User $user, string $provider, SocialUser $socialUser): SocialProfile
    {
        $existing = SocialProfile::where('provider', $provider)
            ->where('provider_id', $socialUser->getId())
            ->first();

        if ($existing && $existing->user_id !== $user->id) {
            throw ValidationException::withMessages([
                'provider' => [__('site.social.already_connected_to_another_account')],
            ]);
        }

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
                'avatar'     => $socialUser->getAvatar(),
                'identifier' => $socialUser->getEmail() ?? $socialUser->getNickname(),
            ]
        );
    }
}
