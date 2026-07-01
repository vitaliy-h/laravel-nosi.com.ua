<?php

namespace Database\Factories;

use App\Modules\Basket\Models\Basket;
use App\Modules\Basket\Models\BasketProduct;
use App\Modules\Shop\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BasketProduct>
 */
class BasketProductFactory extends Factory
{
    /**
     * @var class-string<BasketProduct>
     */
    protected $model = BasketProduct::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'basket_id' => Basket::factory(),
            'shop_product_id' => Product::factory(),
            'product_name' => fake()->words(2, true),
            'product_article' => fake()->bothify('SKU-####'),
            'selling_price' => fake()->randomFloat(2, 1, 500),
            'quantity' => fake()->numberBetween(1, 5),
        ];
    }
}
