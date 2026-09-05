<?php

namespace Database\Factories;

use App\Modules\ShopCart\Models\Cart;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Cart>
 */
class CartFactory extends Factory
{
    /**
     * @var class-string<Cart>
     */
    protected $model = Cart::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id'      => null,
            'total_amount' => 0,
        ];
    }
}
