<?php

namespace App\Modules\Profile\ViewModels;

use App\Modules\Core\Abstract\BaseViewModel;
use App\Modules\User\Models\User;
use App\Modules\UserAuth\Models\SocialProfile;
use Illuminate\Support\Collection;

class ProfileUserViewModel extends BaseViewModel
{
    /**
     * @param  Collection<string, SocialProfileViewModel>  $social_profiles  keyed by provider
     */
    public function __construct(
        public readonly string $formatted_name,
        public readonly ?string $name,
        public readonly ?string $surname,
        public readonly ?string $email,
        public readonly ?string $phone,
        public readonly Collection $social_profiles,
    ) {}

    /**
     * Social accounts come out keyed by provider so the page can ask
     * `$user->social_profiles[$provider]` instead of searching the list.
     * Needs `socialProfiles` eager-loaded; without it nothing shows as connected.
     */
    public static function fromUser(User $user): self
    {
        return new self(
            formatted_name: (string) $user->formatted_name,
            name: $user->name,
            surname: $user->surname,
            email: $user->email,
            phone: $user->phone,
            social_profiles: $user->relationLoaded('socialProfiles')
                ? $user->socialProfiles->keyBy('provider')->map(
                    fn (SocialProfile $profile) => SocialProfileViewModel::from($profile)
                )
                : collect(),
        );
    }
}
