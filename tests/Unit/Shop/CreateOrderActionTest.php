<?php

namespace Tests\Unit\Shop;

use App\Modules\Core\Models\User;
use App\Modules\Shop\Actions\CreateOrderAction;
use App\Modules\Shop\Dtos\CreateOrderData;
use App\Modules\Shop\Models\Order;
use App\Modules\Shop\Models\OrderProduct;
use App\Modules\Shop\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateOrderActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_order_with_items_and_correct_total(): void
    {
        $user = User::factory()->create();
        $productA = Product::factory()->create();
        $productB = Product::factory()->create();

        $dto = CreateOrderData::from([
            'user_id' => $user->id,
            'items'   => [
                [
                    'shop_product_id' => $productA->id,
                    'product_name'    => 'Product A',
                    'product_article'     => 'SKU-A',
                    'selling_price'      => 10.00,
                    'quantity'        => 2,
                ],
                [
                    'shop_product_id' => $productB->id,
                    'product_name'    => 'Product B',
                    'product_article'     => 'SKU-B',
                    'selling_price'      => 5.50,
                    'quantity'        => 3,
                ],
            ],
        ]);

        $order = app(CreateOrderAction::class)->handle($dto);

        $this->assertDatabaseHas('shop_orders', ['id' => $order->id]);
        $this->assertDatabaseCount('shop_order_products', 2);
        $this->assertSame('36.50', (string) $order->total_amount);
    }

    public function test_order_number_equals_id_via_observer(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $dto = CreateOrderData::from([
            'user_id' => $user->id,
            'items'   => [
                [
                    'shop_product_id' => $product->id,
                    'product_name'    => 'Product',
                    'product_article'     => 'SKU-1',
                    'selling_price'      => 10.00,
                    'quantity'        => 1,
                ],
            ],
        ]);

        $order = app(CreateOrderAction::class)->handle($dto);

        $order->refresh();

        $this->assertSame($order->id, (int) $order->order_number);
    }

    public function test_item_total_is_rounded_to_two_decimals(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $dto = CreateOrderData::from([
            'user_id' => $user->id,
            'items'   => [
                [
                    'shop_product_id' => $product->id,
                    'product_name'    => 'Rounded Product',
                    'product_article'     => 'SKU-ROUND',
                    'selling_price'      => 10.005,
                    'quantity'        => 3,
                ],
            ],
        ]);

        $order = app(CreateOrderAction::class)->handle($dto);

        $this->assertDatabaseHas('shop_order_products', [
            'shop_order_id'   => $order->id,
            'shop_product_id' => $product->id,
            'total'           => '30.02',
        ]);
        $this->assertSame('30.02', (string) $order->total_amount);
    }

    public function test_total_is_recalculated_when_item_added_directly(): void
    {
        $order = Order::factory()->create();

        $this->assertSame('0.00', (string) $order->total_amount);

        OrderProduct::factory()->create([
            'shop_order_id' => $order->id,
            'total'         => 12.34,
        ]);

        $order->refresh();

        $this->assertSame('12.34', (string) $order->total_amount);
    }
}
