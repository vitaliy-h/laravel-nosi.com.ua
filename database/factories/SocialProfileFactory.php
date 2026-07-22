<?php

namespace Database\Factories;

use App\Modules\Core\Models\SocialProfile;
use App\Modules\Core\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SocialProfile>
 */
class SocialProfileFactory extends Factory
{
    protected $model = SocialProfile::class;

    public function definition(): array
    {
        return [
            'user_id'          => User::factory(),
            'provider'         => fake()->randomElement(['google', 'facebook', 'telegram']),
            'provider_id'      => fake()->unique()->numerify('##########'),
            'token'            => fake()->sha256(),
            'refresh_token'    => null,
            'token_expires_at' => null,
            'avatar'           => null,
            'identifier'       => fake()->safeEmail(),
        ];
    }

    public function forProvider(string $provider): static
    {
        return $this->state(['provider' => $provider]);
    }

    public function forUser(User $user): static
    {
        return $this->state(['user_id' => $user->id]);
    }
}
