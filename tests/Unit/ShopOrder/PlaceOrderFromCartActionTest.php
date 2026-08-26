<?php

namespace Tests\Unit\ShopOrder;

use App\Modules\Core\Models\User;
use App\Modules\ShopCart\Models\Cart;
use App\Modules\ShopCart\Models\CartProduct;
use App\Modules\ShopOrder\Actions\PlaceOrderFromCartAction;
use App\Modules\ShopOrder\Dtos\CustomerData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlaceOrderFromCartActionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedLetterTemplates();
    }

    public function test_creates_order_with_items_copied_from_cart(): void
    {
        $cart = Cart::factory()->create();
        CartProduct::factory()->create([
            'cart_id' => $cart->id,
            'product_name' => 'Test Product',
            'product_article' => 'SKU-1',
            'selling_price' => 15,
            'quantity' => 3,
            'total' => 45,
        ]);

        $order = app(PlaceOrderFromCartAction::class)->handle($cart, new CustomerData(
            user_name: 'John',
            user_email: 'john@example.com',
        ));

        $this->assertSame(1, $order->items()->count());

        $item = $order->items()->first();
        $this->assertSame('Test Product', $item->product_name);
        $this->assertSame('SKU-1', $item->product_article);
        $this->assertSame(3, $item->quantity);
        $this->assertSame('John', $order->user_name);
        $this->assertSame('john@example.com', $order->user_email);
    }

    public function test_copies_user_id_from_cart_when_present(): void
    {
        $user = User::factory()->create();
        $cart = Cart::factory()->create(['user_id' => $user->id]);
        CartProduct::factory()->create(['cart_id' => $cart->id]);

        $order = app(PlaceOrderFromCartAction::class)->handle($cart, new CustomerData);

        $this->assertSame($user->id, $order->user_id);
    }

    public function test_deletes_the_cart_after_placing_order(): void
    {
        $cart = Cart::factory()->create();
        CartProduct::factory()->create(['cart_id' => $cart->id]);

        app(PlaceOrderFromCartAction::class)->handle($cart, new CustomerData);

        $this->assertDatabaseMissing('shop_carts', ['id' => $cart->id]);
    }
}
