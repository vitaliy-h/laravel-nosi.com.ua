<?php

namespace Tests\Feature\Shop;

use App\Modules\Shop\Enums\PaymentStatusEnum;
use App\Modules\Shop\Enums\PaymentTypeEnum;
use App\Modules\Shop\Models\Brand;
use App\Modules\Shop\Models\Category;
use App\Modules\Shop\Models\Order;
use App\Modules\Shop\Models\OrderProduct;
use App\Modules\Shop\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShopFactoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_factory_creates_valid_nested_set_node(): void
    {
        $category = Category::factory()->create();

        $this->assertModelExists($category);
        $this->assertNull($category->img);
        $this->assertTrue($category->display);
        $this->assertNotNull($category->_lft);
        $this->assertNotNull($category->_rgt);
        $this->assertLessThan($category->_rgt, $category->_lft);
    }

    public function test_category_factory_invisible_state_sets_display_false(): void
    {
        $category = Category::factory()->invisible()->create();

        $this->assertFalse($category->display);
    }

    public function test_brand_factory_creates_brand_without_image(): void
    {
        $brand = Brand::factory()->create();

        $this->assertModelExists($brand);
        $this->assertNull($brand->img);
        $this->assertTrue($brand->display);
    }

    public function test_product_factory_creates_product_with_category_and_no_image(): void
    {
        $product = Product::factory()->create();

        $this->assertModelExists($product);
        $this->assertNull($product->img);
        $this->assertNotNull($product->category_id);
        $this->assertNull($product->brand_id);
        $this->assertDatabaseHas('shop_categories', ['id' => $product->category_id]);
    }

    public function test_product_factory_for_category_state_binds_given_category(): void
    {
        $category = Category::factory()->create();

        $product = Product::factory()->forCategory($category)->create();

        $this->assertSame($category->id, $product->category_id);
    }

    public function test_order_factory_creates_order_and_observer_fills_order_number(): void
    {
        $order = Order::factory()->create();

        $this->assertModelExists($order);
        $this->assertSame(PaymentStatusEnum::UNPAID, $order->payment_status);
        $this->assertSame(PaymentTypeEnum::NONE, $order->payment_type);

        $order->refresh();

        $this->assertSame((string) $order->id, (string) $order->order_number);
    }

    public function test_order_product_factory_binds_order_and_product(): void
    {
        $orderProduct = OrderProduct::factory()->create();

        $this->assertModelExists($orderProduct);
        $this->assertDatabaseHas('shop_orders', ['id' => $orderProduct->shop_order_id]);
        $this->assertDatabaseHas('shop_products', ['id' => $orderProduct->shop_product_id]);
        $this->assertInstanceOf(Order::class, $orderProduct->order);
        $this->assertInstanceOf(Product::class, $orderProduct->product);
    }
}
