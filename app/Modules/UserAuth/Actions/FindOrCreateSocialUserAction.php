<?php

namespace App\Modules\UserAuth\Actions;

use App\Modules\User\Models\User;
use App\Modules\UserAuth\Dtos\RegisterData;
use App\Modules\UserAuth\Models\SocialProfile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as SocialUser;

readonly class FindOrCreateSocialUserAction
{
    public function __construct(private RegisterAction $register) {}

    public function handle(string $provider, SocialUser $socialUser): User
    {
        return DB::transaction(fn (): User => $this->findOrCreate($provider, $socialUser));
    }

    /**
     * Wrapped in a transaction by handle(): creating the user and attaching the
     * social profile that identifies them is one step — a user without the
     * profile would be found by email on the next attempt and silently reused.
     */
    private function findOrCreate(string $provider, SocialUser $socialUser): User
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

        if (!$user) {
            $user = $this->register->handle(new RegisterData(
                name: $socialUser->getName() ?? $socialUser->getNickname() ?? 'User',
                email: $email,
                password: $email ? Str::password(8, symbols: false, spaces: false) : null,
                emailVerifiedAt: $email ? now() : null,
            ));
        }

        $user->socialProfiles()->create([
            'provider'    => $provider,
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
            'token'            => $socialUser->token,
            'refresh_token'    => $socialUser->refreshToken,
            'token_expires_at' => $socialUser->expiresIn
                ? now()->addSeconds($socialUser->expiresIn)
                : null,
            'avatar'     => $socialUser->getAvatar(),
            'identifier' => $socialUser->getEmail() ?? $socialUser->getNickname(),
        ];
    }
}
