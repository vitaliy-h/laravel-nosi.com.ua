<?php

namespace Database\Factories;

use App\Modules\Core\Enums\AuthLogActionEnum;
use App\Modules\Core\Enums\AuthLogResultEnum;
use App\Modules\Core\Enums\AuthLogSourceEnum;
use App\Modules\Core\Models\AuthLog;
use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AuthLog>
 */
class AuthLogFactory extends Factory
{
    protected $model = AuthLog::class;

    public function definition(): array
    {
        return [
            'user_id'    => null,
            'login'      => fake()->safeEmail(),
            'source'     => fake()->randomElement(AuthLogSourceEnum::cases()),
            'action'     => fake()->randomElement(AuthLogActionEnum::cases()),
            'result'     => fake()->randomElement(AuthLogResultEnum::cases()),
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'browser'    => fake()->randomElement(['Chrome', 'Firefox', 'Safari', 'Edge']),
            'platform'   => fake()->randomElement(['Windows', 'macOS', 'Linux', 'Android', 'iOS']),
        ];
    }

    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
            'login'   => $user->email,
        ]);
    }

    public function forAction(AuthLogActionEnum $action): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => $action,
        ]);
    }

    public function forResult(AuthLogResultEnum $result): static
    {
        return $this->state(fn (array $attributes) => [
            'result' => $result,
        ]);
    }
}
