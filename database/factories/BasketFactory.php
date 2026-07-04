<?php

namespace Database\Factories;

use App\Modules\Basket\Models\Basket;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Basket>
 */
class BasketFactory extends Factory
{
    /**
     * @var class-string<Basket>
     */
    protected $model = Basket::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => null,
            'total_amount' => 0,
        ];
    }
}
