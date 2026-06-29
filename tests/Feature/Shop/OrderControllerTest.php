<?php

namespace Tests\Feature\Shop;

use App\Modules\Shop\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_show_order_by_uuid_returns_200(): void
    {
        $order = Order::factory()->create();

        $this->get('/order/' . $order->uuid)->assertOk();
    }

    public function test_show_order_response_contains_cart_structure(): void
    {
        $order = Order::factory()->create();

        $this->get('/order/' . $order->uuid)
            ->assertOk()
            ->assertSee('cart-items', false)
            ->assertSee('cart-summary', false);
    }

    public function test_show_order_with_invalid_uuid_returns_404(): void
    {
        $this->get('/order/00000000-0000-0000-0000-000000000000')->assertNotFound();
    }

    public function test_success_page_returns_200(): void
    {
        $order = Order::factory()->create();

        $this->get('/order/' . $order->uuid . '/success')->assertOk();
    }

    public function test_success_page_contains_success_marker(): void
    {
        $order = Order::factory()->create();

        $this->get('/order/' . $order->uuid . '/success')
            ->assertOk()
            ->assertSee('bi-check-circle-fill', false);
    }

    public function test_success_page_with_invalid_uuid_returns_404(): void
    {
        $this->get('/order/00000000-0000-0000-0000-000000000000/success')->assertNotFound();
    }

    public function test_error_page_returns_200(): void
    {
        $order = Order::factory()->create();

        $this->get('/order/' . $order->uuid . '/error')->assertOk();
    }

    public function test_error_page_contains_error_marker(): void
    {
        $order = Order::factory()->create();

        $this->get('/order/' . $order->uuid . '/error')
            ->assertOk()
            ->assertSee('bi-x-circle-fill', false);
    }

    public function test_error_page_with_invalid_uuid_returns_404(): void
    {
        $this->get('/order/00000000-0000-0000-0000-000000000000/error')->assertNotFound();
    }

    public function test_all_three_routes_are_accessible_without_auth(): void
    {
        $order = Order::factory()->create();

        $this->get('/order/' . $order->uuid)->assertOk();
        $this->get('/order/' . $order->uuid . '/success')->assertOk();
        $this->get('/order/' . $order->uuid . '/error')->assertOk();
    }
}
