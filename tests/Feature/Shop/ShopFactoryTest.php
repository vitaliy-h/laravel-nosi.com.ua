<?php

namespace Tests\Feature\Shop;

use App\Modules\Core\Support\ModuleGate;
use App\Modules\Shop\Models\Category;
use App\Modules\ShopOrders\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShopFactoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_factory_creates_valid_nested_set_node(): void
    {
        $category = Category::factory()->create();

        $this->assertModelExists($category);
        $this->assertNotNull($category->_lft);
        $this->assertNotNull($category->_rgt);
        $this->assertLessThan($category->_rgt, $category->_lft);
    }

    public function test_order_factory_creates_order_and_observer_fills_order_number(): void
    {
        if (!ModuleGate::isEnabled('shop_orders')) {
            $this->markTestSkipped('Shop orders module is disabled');
        }

        $order = Order::factory()->create();

        $this->assertModelExists($order);

        $order->refresh();

        $this->assertSame((string) $order->id, (string) $order->order_number);
    }
}
