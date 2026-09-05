<?php

namespace Tests\Unit\ShopCart;

use App\Modules\ShopCart\Models\Cart;
use App\Modules\ShopCart\Models\CartProduct;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartProductObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_a_line_recalculates_the_cart_total(): void
    {
        $cart = Cart::factory()->create();

        $this->lineFor($cart, 30);

        $this->assertSame('30.00', $cart->fresh()->total_amount);
    }

    public function test_deleting_a_line_recalculates_the_cart_total(): void
    {
        $cart = Cart::factory()->create();
        $kept = $this->lineFor($cart, 30);
        $removed = $this->lineFor($cart, 12.5);

        $this->assertSame('42.50', $cart->fresh()->total_amount);

        $removed->delete();

        $this->assertSame('30.00', $cart->fresh()->total_amount);
        $this->assertSame('30.00', $cart->fresh()->subtotal_amount);
        $this->assertModelExists($kept);
    }

    public function test_deleting_the_last_line_zeroes_the_cart_total(): void
    {
        $cart = Cart::factory()->create();
        $line = $this->lineFor($cart, 99.99);

        $line->delete();

        $this->assertSame('0.00', $cart->fresh()->total_amount);
    }

    private function lineFor(Cart $cart, float $total): CartProduct
    {
        return CartProduct::factory()->create([
            'cart_id'       => $cart->id,
            'selling_price' => $total,
            'quantity'      => 1,
            'total'         => $total,
        ]);
    }
}
