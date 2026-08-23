<?php

namespace Database\Factories;

use App\Modules\Subscribers\Models\Subscriber;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subscriber>
 */
class SubscriberFactory extends Factory
{
    protected $model = Subscriber::class;

    public function definition(): array
    {
        return [
            'email' => fake()->unique()->safeEmail(),
            'ip' => fake()->ipv4(),
            'integrated_at' => null,
            'error' => null,
        ];
    }

    public function integrated(): static
    {
        return $this->state(fn (array $attributes) => [
            'integrated_at' => now(),
            'error' => null,
        ]);
    }

    public function withError(): static
    {
        return $this->state(fn (array $attributes) => [
            'integrated_at' => null,
            'error' => 'CRM responded with HTTP 500',
        ]);
    }
}
