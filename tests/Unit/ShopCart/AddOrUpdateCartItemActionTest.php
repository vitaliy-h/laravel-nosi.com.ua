<?php

namespace Tests\Unit\ShopCart;

use App\Modules\ShopCart\Actions\AddOrUpdateCartItemAction;
use App\Modules\ShopCart\Models\Cart;
use App\Modules\ShopCart\Models\CartProduct;
use App\Modules\Shop\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AddOrUpdateCartItemActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_new_cart_item_with_product_snapshot(): void
    {
        $cart = Cart::factory()->create();
        $product = Product::factory()->create(['title' => 'Widget', 'article' => 'ART-1', 'price' => 10.5]);

        $item = app(AddOrUpdateCartItemAction::class)->handle($cart, $product, 3);

        $this->assertInstanceOf(CartProduct::class, $item);
        $this->assertSame($cart->id, $item->cart_id);
        $this->assertSame($product->id, $item->shop_product_id);
        $this->assertSame('Widget', $item->product_name);
        $this->assertSame('ART-1', $item->product_article);
        $this->assertSame('10.50', (string) $item->selling_price);
        $this->assertSame(3, $item->quantity);
        $this->assertSame('31.50', (string) $item->total);
    }

    public function test_updates_existing_item_quantity_instead_of_duplicating(): void
    {
        $cart = Cart::factory()->create();
        $product = Product::factory()->create(['price' => 10]);

        $action = app(AddOrUpdateCartItemAction::class);
        $action->handle($cart, $product, 1);
        $item = $action->handle($cart, $product, 5);

        $this->assertDatabaseCount('shop_cart_products', 1);
        $this->assertSame(5, $item->quantity);
        $this->assertSame('50.00', (string) $item->total);
    }

    public function test_total_is_rounded_to_two_decimals(): void
    {
        $cart = Cart::factory()->create();
        $product = Product::factory()->create(['price' => 10.005]);

        $item = app(AddOrUpdateCartItemAction::class)->handle($cart, $product, 3);

        $this->assertSame('30.02', (string) $item->total);
    }
}
