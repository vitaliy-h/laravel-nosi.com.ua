<?php

namespace Database\Factories;

use App\Modules\Core\Enums\LogEventEnum;
use App\Modules\Core\Enums\LogSourceEnum;
use App\Modules\Core\Models\Log;
use App\Modules\Core\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Log>
 */
class LogFactory extends Factory
{
    protected $model = Log::class;

    public function definition(): array
    {
        return [
            'user_id'        => null,
            'event'          => fake()->randomElement(LogEventEnum::cases()),
            'model_type'     => User::class,
            'model_id'       => fake()->numberBetween(1, 100),
            'old_values'     => null,
            'new_values'     => null,
            'changed_fields' => null,
            'source'         => fake()->randomElement(LogSourceEnum::cases()),
            'ip_address'     => fake()->ipv4(),
        ];
    }

    public function forUser(User $user): static
    {
        return $this->state(fn(array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    public function forEvent(LogEventEnum $event): static
    {
        return $this->state(fn(array $attributes) => [
            'event' => $event,
        ]);
    }
}
