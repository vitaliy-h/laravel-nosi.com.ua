<?php

namespace Database\Factories;

use App\Modules\Shop\Models\Product;
use App\Modules\ShopCart\Models\Cart;
use App\Modules\ShopCart\Models\CartProduct;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CartProduct>
 */
class CartProductFactory extends Factory
{
    /**
     * @var class-string<CartProduct>
     */
    protected $model = CartProduct::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cart_id'         => Cart::factory(),
            'shop_product_id' => Product::factory(),
            'product_name'    => fake()->words(2, true),
            'product_article' => fake()->bothify('SKU-####'),
            'selling_price'   => fake()->randomFloat(2, 1, 500),
            'quantity'        => fake()->numberBetween(1, 5),
        ];
    }
}
