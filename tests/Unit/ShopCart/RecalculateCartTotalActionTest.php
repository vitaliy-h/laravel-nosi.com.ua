<?php

namespace Tests\Unit\ShopCart;

use App\Modules\ShopCart\Actions\RecalculateCartTotalAction;
use App\Modules\ShopCart\Models\Cart;
use App\Modules\ShopCart\Models\CartProduct;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecalculateCartTotalActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_sums_item_totals_into_cart_total(): void
    {
        $cart = Cart::factory()->create();
        CartProduct::factory()->create(['cart_id' => $cart->id, 'total' => 10.5]);
        CartProduct::factory()->create(['cart_id' => $cart->id, 'total' => 5.25]);

        app(RecalculateCartTotalAction::class)->handle($cart);

        $this->assertSame('15.75', (string) $cart->fresh()->total_amount);
    }

    public function test_empty_cart_totals_zero(): void
    {
        $cart = Cart::factory()->create(['total_amount' => 99]);

        app(RecalculateCartTotalAction::class)->handle($cart);

        $this->assertSame('0.00', (string) $cart->fresh()->total_amount);
    }
}
