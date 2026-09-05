<?php

namespace Database\Factories;

use App\Modules\ShopCart\Enums\PromocodeTypeEnum;
use App\Modules\ShopCart\Models\Promocode;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Promocode>
 */
class PromocodeFactory extends Factory
{
    /**
     * @var class-string<Promocode>
     */
    protected $model = Promocode::class;

    /**
     * Active percent code by default; use the states below for the cases that
     * must fail validation.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code'             => Str::upper(fake()->unique()->bothify('SAVE##??')),
            'type'             => PromocodeTypeEnum::PERCENT,
            'value'            => 10,
            'min_order_amount' => null,
            'max_discount'     => null,
            'starts_at'        => null,
            'expires_at'       => null,
            'usage_limit'      => null,
            'used_count'       => 0,
            'display'          => true,
            'comment'          => null,
        ];
    }

    public function fixed(float $value = 10): static
    {
        return $this->state(fn (array $attributes) => [
            'type'  => PromocodeTypeEnum::FIXED,
            'value' => $value,
        ]);
    }

    public function percent(float $value = 10): static
    {
        return $this->state(fn (array $attributes) => [
            'type'  => PromocodeTypeEnum::PERCENT,
            'value' => $value,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'starts_at'  => now()->subMonth(),
            'expires_at' => now()->subDay(),
        ]);
    }

    public function notStarted(): static
    {
        return $this->state(fn (array $attributes) => [
            'starts_at'  => now()->addWeek(),
            'expires_at' => now()->addMonth(),
        ]);
    }

    public function exhausted(int $limit = 5): static
    {
        return $this->state(fn (array $attributes) => [
            'usage_limit' => $limit,
            'used_count'  => $limit,
        ]);
    }

    public function withMinOrder(float $amount): static
    {
        return $this->state(fn (array $attributes) => [
            'min_order_amount' => $amount,
        ]);
    }

    public function invisible(): static
    {
        return $this->state(fn (array $attributes) => [
            'display' => false,
        ]);
    }
}
