<?php

namespace Tests\Feature\Shop;

use App\Modules\Basket\Models\Basket;
use App\Modules\Basket\Models\BasketProduct;
use App\Modules\Orders\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlaceOrderControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_place_order_creates_order_and_redirects_to_show(): void
    {
        $basket = Basket::factory()->create();
        BasketProduct::factory()->create([
            'basket_id' => $basket->id,
            'quantity' => 2,
            'selling_price' => 10,
            'total' => 20,
        ]);

        $response = $this->post(route('order.create'), [
            'basket_uuid' => $basket->uuid,
            'user_name' => 'John',
            'user_surname' => 'Doe',
            'user_email' => 'john@example.com',
            'user_phone' => '+123456789',
            'comment' => 'Please call before delivery',
        ]);

        $order = Order::firstOrFail();

        $response->assertRedirect(route('order.show', $order->uuid));

        $this->assertSame('John', $order->user_name);
        $this->assertSame('Doe', $order->user_surname);
        $this->assertSame('john@example.com', $order->user_email);
        $this->assertSame('+123456789', $order->user_phone);
        $this->assertSame('Please call before delivery', $order->comment);
        $this->assertSame(1, $order->items()->count());
    }

    public function test_place_order_deletes_the_basket(): void
    {
        $basket = Basket::factory()->create();
        BasketProduct::factory()->create(['basket_id' => $basket->id]);

        $this->post(route('order.create'), [
            'basket_uuid' => $basket->uuid,
            'user_email' => 'john@example.com',
        ]);

        $this->assertDatabaseMissing('shop_baskets', ['id' => $basket->id]);
    }

    public function test_place_order_requires_email(): void
    {
        $basket = Basket::factory()->create();
        BasketProduct::factory()->create(['basket_id' => $basket->id]);

        $this->post(route('order.create'), [
            'basket_uuid' => $basket->uuid,
        ])->assertSessionHasErrors('user_email');
    }

    public function test_place_order_requires_valid_email(): void
    {
        $basket = Basket::factory()->create();
        BasketProduct::factory()->create(['basket_id' => $basket->id]);

        $this->post(route('order.create'), [
            'basket_uuid' => $basket->uuid,
            'user_email' => 'not-an-email',
        ])->assertSessionHasErrors('user_email');
    }

    public function test_place_order_requires_basket_uuid(): void
    {
        $this->post(route('order.create'), [
            'user_email' => 'john@example.com',
        ])->assertSessionHasErrors('basket_uuid');
    }

    public function test_place_order_with_unknown_basket_uuid_fails_validation(): void
    {
        $this->post(route('order.create'), [
            'basket_uuid' => '00000000-0000-0000-0000-000000000000',
            'user_email' => 'john@example.com',
        ])->assertSessionHasErrors('basket_uuid');
    }
}
