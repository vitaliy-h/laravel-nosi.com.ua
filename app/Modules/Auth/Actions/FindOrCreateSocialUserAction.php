<?php

namespace App\Modules\Auth\Actions;

use App\Modules\Auth\Dtos\RegisterData;
use App\Modules\Core\Models\SocialProfile;
use App\Modules\Core\Models\User;
use Laravel\Socialite\Contracts\User as SocialUser;

readonly class FindOrCreateSocialUserAction
{
    public function __construct(private RegisterAction $register) {}

    public function handle(string $provider, SocialUser $socialUser): User
    {
        $existing = SocialProfile::where('provider', $provider)
            ->where('provider_id', $socialUser->getId())
            ->with('user')
            ->first();

        if ($existing) {
            $existing->update([
                'token'            => $socialUser->token,
                'refresh_token'    => $socialUser->refreshToken,
                'token_expires_at' => $socialUser->expiresIn
                    ? now()->addSeconds($socialUser->expiresIn)
                    : null,
                'avatar'           => $socialUser->getAvatar(),
            ]);

            return $existing->user;
        }

        $email = $socialUser->getEmail();

        $user = $email
            ? User::where('email', $email)->first()
            : null;

        if (!$user) {
            $user = $this->register->handle(new RegisterData(
                name:              $socialUser->getName() ?? $socialUser->getNickname() ?? 'User',
                email:             $email,
                emailVerifiedAt:   $email ? now() : null,
            ));
        }

        $user->socialProfiles()->create([
            'provider'         => $provider,
            'provider_id'      => $socialUser->getId(),
            'token'            => $socialUser->token,
            'refresh_token'    => $socialUser->refreshToken,
            'token_expires_at' => $socialUser->expiresIn
                ? now()->addSeconds($socialUser->expiresIn)
                : null,
            'avatar'           => $socialUser->getAvatar(),
        ]);

        return $user;
    }
}
