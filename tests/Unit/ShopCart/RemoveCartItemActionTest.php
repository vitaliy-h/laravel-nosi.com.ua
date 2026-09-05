<?php

namespace Tests\Unit\ShopCart;

use App\Modules\Shop\Models\Product;
use App\Modules\ShopCart\Actions\RemoveCartItemAction;
use App\Modules\ShopCart\Models\Cart;
use App\Modules\ShopCart\Models\CartProduct;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RemoveCartItemActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_removes_item_and_recalculates_total(): void
    {
        $cart = Cart::factory()->create();
        $product = Product::factory()->create();
        $item = CartProduct::factory()->create([
            'cart_id'         => $cart->id,
            'shop_product_id' => $product->id,
            'total'           => 20,
        ]);
        CartProduct::factory()->create(['cart_id' => $cart->id, 'total' => 5]);

        app(RemoveCartItemAction::class)->handle($cart, $product);

        $this->assertModelMissing($item);
        $this->assertSame('5.00', (string) $cart->fresh()->total_amount);
    }

    public function test_removing_nonexistent_item_is_a_noop(): void
    {
        $cart = Cart::factory()->create();
        $product = Product::factory()->create();
        CartProduct::factory()->create(['cart_id' => $cart->id, 'total' => 5]);

        app(RemoveCartItemAction::class)->handle($cart, $product);

        $this->assertSame(1, $cart->items()->count());
        $this->assertSame('5.00', (string) $cart->fresh()->total_amount);
    }
}
