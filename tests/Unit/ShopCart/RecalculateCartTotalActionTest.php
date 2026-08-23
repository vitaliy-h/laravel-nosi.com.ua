<?php

namespace Tests\Unit\ShopCart;

use App\Modules\ShopCart\Actions\RecalculateCartTotalAction;
use App\Modules\ShopCart\Models\Cart;
use App\Modules\ShopCart\Models\CartProduct;
use App\Modules\ShopCart\Models\Promocode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecalculateCartTotalActionTest extends TestCase
{
    use RefreshDatabase;

    private RecalculateCartTotalAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(RecalculateCartTotalAction::class);
    }

    public function test_sums_item_totals_into_cart_total(): void
    {
        $cart = Cart::factory()->create();
        CartProduct::factory()->create(['cart_id' => $cart->id, 'total' => 10.5]);
        CartProduct::factory()->create(['cart_id' => $cart->id, 'total' => 5.25]);

        $this->action->handle($cart);

        $this->assertSame('15.75', (string) $cart->fresh()->total_amount);
    }

    public function test_empty_cart_totals_zero(): void
    {
        $cart = Cart::factory()->create(['total_amount' => 99]);

        $this->action->handle($cart);

        $this->assertSame('0.00', (string) $cart->fresh()->total_amount);
    }

    public function test_total_is_subtotal_minus_discount(): void
    {
        $promocode = Promocode::factory()->fixed(20)->create(['code' => 'MINUS20']);
        $cart = $this->cartWithItemTotal(100, [
            'promocode_id' => $promocode->id,
            'promocode_code' => 'MINUS20',
        ]);

        $this->action->handle($cart);

        $cart->refresh();

        $this->assertSame('100.00', $cart->subtotal_amount);
        $this->assertSame('20.00', $cart->discount_amount);
        $this->assertSame('80.00', $cart->total_amount);
    }

    public function test_total_equals_subtotal_without_promocode(): void
    {
        $cart = $this->cartWithItemTotal(75.50);

        $this->action->handle($cart);

        $cart->refresh();

        $this->assertSame('0.00', $cart->discount_amount);
        $this->assertSame('75.50', $cart->total_amount);
    }

    public function test_promocode_is_dropped_when_cart_falls_below_min_order_amount(): void
    {
        $promocode = Promocode::factory()->fixed(20)->withMinOrder(100)->create(['code' => 'BIG']);
        $cart = $this->cartWithItemTotal(50, [
            'promocode_id' => $promocode->id,
            'promocode_code' => 'BIG',
        ]);

        $this->action->handle($cart);

        $cart->refresh();

        $this->assertNull($cart->promocode_id);
        $this->assertNull($cart->promocode_code);
        $this->assertSame('0.00', $cart->discount_amount);
        $this->assertSame('50.00', $cart->total_amount);
    }

    public function test_expired_promocode_is_dropped(): void
    {
        $promocode = Promocode::factory()->fixed(20)->expired()->create(['code' => 'OLD']);
        $cart = $this->cartWithItemTotal(100, [
            'promocode_id' => $promocode->id,
            'promocode_code' => 'OLD',
        ]);

        $this->action->handle($cart);

        $cart->refresh();

        $this->assertNull($cart->promocode_id);
        $this->assertSame('100.00', $cart->total_amount);
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
