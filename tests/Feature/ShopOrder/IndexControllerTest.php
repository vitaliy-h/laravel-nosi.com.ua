<?php

namespace Tests\Feature\ShopOrder;

use App\Modules\Shop\Models\Category;
use App\Modules\Shop\Models\Product;
use App\Modules\ShopOrder\Models\Order;
use App\Modules\ShopOrder\Models\OrderProduct;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndexControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_show_order_by_uuid_returns_200(): void
    {
        $order = Order::factory()->create();

        $this->get('/orders/' . $order->uuid)->assertOk();
    }

    public function test_show_order_response_contains_cart_structure(): void
    {
        $order = Order::factory()->create();

        $this->get('/orders/' . $order->uuid)
            ->assertOk()
            ->assertSee('cart-items', false)
            ->assertSee('cart-summary', false);
    }

    public function test_show_order_lists_its_items(): void
    {
        $order = Order::factory()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->forCategory($category)->create();

        $item = OrderProduct::factory()->create([
            'shop_order_id'   => $order->id,
            'shop_product_id' => $product->id,
            'product_name'    => 'Snapshot Name',
            'product_article' => 'SKU-1',
        ]);

        // The line renders its own snapshot of name and article, not the
        // product's current title.
        $product->update(['title' => 'Renamed Later']);

        $this->get('/orders/' . $order->uuid)
            ->assertOk()
            ->assertSee('Snapshot Name')
            ->assertDontSee('Renamed Later')
            ->assertSee('SKU-1')
            ->assertSee(save_route('shop.product', [$category->slug, $product->slug_with_id]), false)
            ->assertSee($item->total_formatted, false);
    }

    public function test_show_order_with_invalid_uuid_returns_404(): void
    {
        $this->get('/orders/00000000-0000-0000-0000-000000000000')->assertNotFound();
    }

    public function test_success_page_returns_200(): void
    {
        $order = Order::factory()->create();

        $this->get('/orders/' . $order->uuid . '/success')->assertOk();
    }

    public function test_success_page_contains_success_marker(): void
    {
        $order = Order::factory()->create();

        $this->get('/orders/' . $order->uuid . '/success')
            ->assertOk()
            ->assertSee('bi-check-circle-fill', false);
    }

    public function test_success_page_with_invalid_uuid_returns_404(): void
    {
        $this->get('/orders/00000000-0000-0000-0000-000000000000/success')->assertNotFound();
    }

    public function test_error_page_returns_200(): void
    {
        $order = Order::factory()->create();

        $this->get('/orders/' . $order->uuid . '/error')->assertOk();
    }

    public function test_error_page_contains_error_marker(): void
    {
        $order = Order::factory()->create();

        $this->get('/orders/' . $order->uuid . '/error')
            ->assertOk()
            ->assertSee('bi-x-circle-fill', false);
    }

    public function test_error_page_with_invalid_uuid_returns_404(): void
    {
        $this->get('/orders/00000000-0000-0000-0000-000000000000/error')->assertNotFound();
    }

    public function test_all_three_routes_are_accessible_without_auth(): void
    {
        $order = Order::factory()->create();

        $this->get('/orders/' . $order->uuid)->assertOk();
        $this->get('/orders/' . $order->uuid . '/success')->assertOk();
        $this->get('/orders/' . $order->uuid . '/error')->assertOk();
    }
}
