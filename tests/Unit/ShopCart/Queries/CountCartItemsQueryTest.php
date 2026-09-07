<?php

namespace Tests\Unit\ShopCart\Queries;

use App\Modules\ShopCart\Models\Cart;
use App\Modules\ShopCart\Models\CartProduct;
use App\Modules\ShopCart\Queries\CountCartItemsQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CountCartItemsQueryTest extends TestCase
{
    use RefreshDatabase;

    public function test_counts_quantities_not_rows(): void
    {
        $cart = Cart::factory()->create();
        CartProduct::factory()->create(['cart_id' => $cart->id, 'quantity' => 2]);
        CartProduct::factory()->create(['cart_id' => $cart->id, 'quantity' => 3]);

        $this->assertSame(5, app(CountCartItemsQuery::class)->handle($cart));
    }

    public function test_empty_cart_counts_zero(): void
    {
        $this->assertSame(0, app(CountCartItemsQuery::class)->handle(Cart::factory()->create()));
    }

    public function test_no_cart_counts_zero(): void
    {
        $this->assertSame(0, app(CountCartItemsQuery::class)->handle(null));
    }
}
