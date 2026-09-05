<?php

namespace Tests\Unit\ShopOrder\ViewModels;

use App\Modules\Shop\Models\Category;
use App\Modules\Shop\Models\Product;
use App\Modules\ShopOrder\Models\Order;
use App\Modules\ShopOrder\Models\OrderProduct;
use App\Modules\ShopOrder\ViewModels\OrderViewModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderViewModelTest extends TestCase
{
    use RefreshDatabase;

    private function loaded(Order $order): Order
    {
        return $order->load('items.product.category');
    }

    public function test_the_customer_name_is_joined_from_name_and_surname(): void
    {
        $order = Order::factory()->create(['user_name' => 'Ada', 'user_surname' => 'Lovelace']);

        $this->assertSame('Ada Lovelace', OrderViewModel::from($this->loaded($order))->customer_name);
    }

    /**
     * Half a name is still a name; no name at all is null, so the page can skip
     * the row instead of rendering an empty one.
     */
    public function test_a_missing_surname_still_yields_a_name(): void
    {
        $order = Order::factory()->create(['user_name' => 'Ada', 'user_surname' => null]);

        $this->assertSame('Ada', OrderViewModel::from($this->loaded($order))->customer_name);
    }

    public function test_an_order_without_a_customer_name_reports_null(): void
    {
        $order = Order::factory()->create(['user_name' => null, 'user_surname' => null]);

        $this->assertNull(OrderViewModel::from($this->loaded($order))->customer_name);
    }

    public function test_payment_status_is_exposed_as_value_and_label(): void
    {
        $order = Order::factory()->create();

        $vm = OrderViewModel::from($this->loaded($order));

        $this->assertSame($order->payment_status->value, $vm->payment_status);
        $this->assertSame($order->payment_status->label(), $vm->payment_status_label);
    }

    public function test_a_line_links_back_to_the_catalogue(): void
    {
        $order = Order::factory()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->forCategory($category)->create();

        OrderProduct::factory()->create([
            'shop_order_id'   => $order->id,
            'shop_product_id' => $product->id,
            'product_name'    => 'Name When Ordered',
            'product_article' => 'SKU-7',
        ]);

        $vm = OrderViewModel::from($this->loaded($order));
        $item = $vm->items->first();

        $this->assertSame(1, $vm->items_count);
        $this->assertSame('Name When Ordered', $item->name);
        $this->assertSame('SKU-7', $item->article);
        $this->assertSame(
            save_route('shop.product', [$category->slug, $product->slug_with_id]),
            $item->product_url
        );
    }

    public function test_discount_is_flagged_only_when_there_is_one(): void
    {
        $without = Order::factory()->create(['discount_amount' => 0]);
        $with = Order::factory()->create(['discount_amount' => 5, 'promocode_code' => 'SAVE5']);

        $this->assertFalse(OrderViewModel::from($this->loaded($without))->has_discount);
        $this->assertTrue(OrderViewModel::from($this->loaded($with))->has_discount);
    }
}
