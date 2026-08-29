<?php

namespace App\Modules\Profile\ViewModels;

use App\Modules\Core\Abstract\BaseViewModel;
use App\Modules\UserAuth\Models\SocialProfile;

/**
 * A connected social account, as the profile page shows it. Tokens stay out —
 * the page renders only who the account belongs to.
 */
class SocialProfileViewModel extends BaseViewModel
{
    public function __construct(
        public readonly string $provider,
        public readonly ?string $identifier,
    ) {}

    public static function fromSocialProfile(SocialProfile $profile): self
    {
        return new self(
            provider: (string) $profile->provider,
            identifier: $profile->identifier,
        );
    }
}
