<?php

namespace Database\Factories;

use App\Modules\Feedback\Models\Feedback;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Feedback>
 */
class FeedbackFactory extends Factory
{
    protected $model = Feedback::class;

    public function definition(): array
    {
        return [
            'name'      => fake()->name(),
            'email'     => fake()->unique()->safeEmail(),
            'ip'        => fake()->ipv4(),
            'message'   => fake()->paragraph(),
            'processed' => false,
        ];
    }

    public function processed(): static
    {
        return $this->state(fn (array $attributes) => [
            'processed' => true,
        ]);
    }
}
