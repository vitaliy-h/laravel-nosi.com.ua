<?php

namespace Tests\Feature\ShopOrders;

use App\Modules\ShopCart\Models\Cart;
use App\Modules\ShopCart\Models\CartProduct;
use App\Modules\ShopCart\Models\Promocode;
use App\Modules\ShopOrders\Actions\PlaceOrderFromCartAction;
use App\Modules\ShopOrders\Dtos\CustomerData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlaceOrderWithPromocodeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedPagesForRouting();
        $this->seedLetterTemplates();
    }

    public function test_promocode_moves_from_cart_to_order(): void
    {
        $promocode = Promocode::factory()->fixed(20)->create(['code' => 'MINUS20']);
        $cart = $this->cartWithItemTotal(100, [
            'promocode_id' => $promocode->id,
            'promocode_code' => 'MINUS20',
        ]);

        $order = app(PlaceOrderFromCartAction::class)->handle($cart, $this->customer());

        $this->assertDatabaseHas('shop_orders', [
            'id' => $order->id,
            'promocode_id' => $promocode->id,
            'promocode_code' => 'MINUS20',
            'subtotal_amount' => 100.00,
            'discount_amount' => 20.00,
            'total_amount' => 80.00,
        ]);
    }

    public function test_placing_an_order_increments_the_promocode_usage(): void
    {
        $promocode = Promocode::factory()->fixed(20)->create(['code' => 'MINUS20']);
        $cart = $this->cartWithItemTotal(100, [
            'promocode_id' => $promocode->id,
            'promocode_code' => 'MINUS20',
        ]);

        app(PlaceOrderFromCartAction::class)->handle($cart, $this->customer());

        $this->assertSame(1, $promocode->refresh()->used_count);
    }

    public function test_promocode_expired_before_checkout_is_dropped(): void
    {
        $promocode = Promocode::factory()->fixed(20)->expired()->create(['code' => 'OLD']);
        $cart = $this->cartWithItemTotal(100, [
            'promocode_id' => $promocode->id,
            'promocode_code' => 'OLD',
        ]);

        $order = app(PlaceOrderFromCartAction::class)->handle($cart, $this->customer());

        $this->assertDatabaseHas('shop_orders', [
            'id' => $order->id,
            'promocode_id' => null,
            'promocode_code' => null,
            'discount_amount' => 0.00,
            'total_amount' => 100.00,
        ]);
        $this->assertSame(0, $promocode->refresh()->used_count);
    }

    public function test_order_without_promocode_keeps_full_total(): void
    {
        $cart = $this->cartWithItemTotal(100);

        $order = app(PlaceOrderFromCartAction::class)->handle($cart, $this->customer());

        $this->assertDatabaseHas('shop_orders', [
            'id' => $order->id,
            'discount_amount' => 0.00,
            'total_amount' => 100.00,
        ]);
    }

    private function customer(): CustomerData
    {
        return new CustomerData(
            user_name: 'John',
            user_surname: 'Doe',
            user_email: 'john@example.com',
            user_phone: '+10000000000',
            comment: null,
        );
    }

    /**
     * @param  array<string, mixed>  $cartAttributes
     */
    private function cartWithItemTotal(float $total, array $cartAttributes = []): Cart
    {
        $cart = Cart::factory()->create($cartAttributes);

        CartProduct::factory()->create([
            'cart_id' => $cart->id,
            'selling_price' => $total,
            'quantity' => 1,
            'total' => $total,
        ]);

        return $cart->refresh();
    }
}
