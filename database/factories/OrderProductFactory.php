<?php

namespace Database\Factories;

use App\Modules\Shop\Models\Order;
use App\Modules\Shop\Models\OrderProduct;
use App\Modules\Shop\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderProduct>
 */
class OrderProductFactory extends Factory
{
    /**
     * @var class-string<OrderProduct>
     */
    protected $model = OrderProduct::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'shop_order_id' => Order::factory(),
            'shop_product_id' => Product::factory(),
            'product_name' => fake()->words(2, true),
            'product_sku' => fake()->bothify('SKU-####'),
            'sale_price' => fake()->randomFloat(2, 1, 500),
            'quantity' => fake()->numberBetween(1, 5),
        ];
    }
}
