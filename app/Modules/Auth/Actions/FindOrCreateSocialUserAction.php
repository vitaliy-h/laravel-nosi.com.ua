<?php

namespace App\Modules\Auth\Actions;

use App\Modules\Auth\Dtos\RegisterData;
use App\Modules\Core\Models\SocialProfile;
use App\Modules\Core\Models\User;
use Illuminate\Support\Str;
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
            $existing->update($this->socialProfileData($socialUser));

            return $existing->user;
        }

        $email = $socialUser->getEmail();

        $user = $email
            ? User::where('email', $email)->first()
            : null;

        if (! $user) {
            $user = $this->register->handle(new RegisterData(
                name: $socialUser->getName() ?? $socialUser->getNickname() ?? 'User',
                email: $email,
                password: $email ? Str::password() : null,
                emailVerifiedAt: $email ? now() : null,
            ));
        }

        $user->socialProfiles()->create([
            'provider' => $provider,
            'provider_id' => $socialUser->getId(),
            ...$this->socialProfileData($socialUser),
        ]);

        return $user;
    }

    /**
     * Token/avatar fields shared between creating and refreshing a social profile.
     */
    private function socialProfileData(SocialUser $socialUser): array
    {
        return [
            'token' => $socialUser->token,
            'refresh_token' => $socialUser->refreshToken,
            'token_expires_at' => $socialUser->expiresIn
                ? now()->addSeconds($socialUser->expiresIn)
                : null,
            'avatar' => $socialUser->getAvatar(),
        ];
    }
}
