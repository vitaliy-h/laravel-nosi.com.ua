<?php

namespace Database\Factories;

use App\Modules\Review\Models\Review;
use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Review>
 */
class ReviewFactory extends Factory
{
    /**
     * @var class-string<Review>
     */
    protected $model = Review::class;

    /**
     * Visible by default so rating assertions stay short; use invisible() for
     * the moderation queue.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'display' => true,
            'user_id' => null,
            'rating'  => fake()->numberBetween(1, 5),
            'name'    => fake()->name(),
            'city'    => fake()->city(),
            'email'   => fake()->safeEmail(),
            'text'    => fake()->paragraph(),
            'ip'      => fake()->ipv4(),
        ];
    }

    public function invisible(): static
    {
        return $this->state(fn (array $attributes) => [
            'display' => false,
        ]);
    }

    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    public function withRating(int $rating): static
    {
        return $this->state(fn (array $attributes) => [
            'rating' => $rating,
        ]);
    }
}
