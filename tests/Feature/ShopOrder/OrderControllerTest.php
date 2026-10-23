<?php

namespace Tests\Feature\ShopOrder;

use App\Modules\ShopCart\Models\Cart;
use App\Modules\ShopCart\Models\CartProduct;
use App\Modules\ShopOrder\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedLetterTemplates();
    }

    public function test_place_order_creates_order_and_redirects_to_show(): void
    {
        $cart = Cart::factory()->create();
        CartProduct::factory()->create([
            'cart_id'       => $cart->id,
            'quantity'      => 2,
            'selling_price' => 10,
            'total'         => 20,
        ]);

        $response = $this->post(route('order.create'), [
            'cart_uuid'    => $cart->uuid,
            'user_name'    => 'John',
            'user_surname' => 'Doe',
            'user_email'   => 'john@example.com',
            'user_phone'   => '+123456789',
            'comment'      => 'Please call before delivery',
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

    public function test_place_order_deletes_the_cart(): void
    {
        $cart = Cart::factory()->create();
        CartProduct::factory()->create(['cart_id' => $cart->id]);

        $this->post(route('order.create'), [
            'cart_uuid'  => $cart->uuid,
            'user_email' => 'john@example.com',
        ]);

        $this->assertDatabaseMissing('shop_carts', ['id' => $cart->id]);
    }

    public function test_place_order_requires_email(): void
    {
        $cart = Cart::factory()->create();
        CartProduct::factory()->create(['cart_id' => $cart->id]);

        $this->post(route('order.create'), [
            'cart_uuid' => $cart->uuid,
        ])->assertSessionHasErrors('user_email');
    }

    public function test_place_order_requires_valid_email(): void
    {
        $cart = Cart::factory()->create();
        CartProduct::factory()->create(['cart_id' => $cart->id]);

        $this->post(route('order.create'), [
            'cart_uuid'  => $cart->uuid,
            'user_email' => 'not-an-email',
        ])->assertSessionHasErrors('user_email');
    }

    public function test_place_order_rejects_email_without_domain_zone(): void
    {
        $cart = Cart::factory()->create();
        CartProduct::factory()->create(['cart_id' => $cart->id]);

        $this->post(route('order.create'), [
            'cart_uuid'  => $cart->uuid,
            'user_email' => 'john@example',
        ])->assertSessionHasErrors('user_email');

        $this->assertSame(0, Order::count());
    }

    public function test_place_order_requires_cart_uuid(): void
    {
        $this->post(route('order.create'), [
            'user_email' => 'john@example.com',
        ])->assertSessionHasErrors('cart_uuid');
    }

    public function test_place_order_with_unknown_cart_uuid_fails_validation(): void
    {
        $this->post(route('order.create'), [
            'cart_uuid'  => '00000000-0000-0000-0000-000000000000',
            'user_email' => 'john@example.com',
        ])->assertSessionHasErrors('cart_uuid');
    }
}
