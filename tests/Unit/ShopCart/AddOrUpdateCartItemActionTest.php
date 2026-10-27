<?php

namespace Tests\Unit\ShopCart;

use App\Modules\Shop\Models\Product;
use App\Modules\ShopCart\Actions\AddOrUpdateCartItemAction;
use App\Modules\ShopCart\Models\Cart;
use App\Modules\ShopCart\Models\CartProduct;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
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

    public function test_add_puts_more_sets_on_top_of_the_line_of_that_set_only(): void
    {
        $cart = Cart::factory()->create();
        $product = Product::factory()->create(['packs' => [['quantity' => 1, 'price' => 10], ['quantity' => 5, 'price' => 45]]]);

        $action = app(AddOrUpdateCartItemAction::class);
        $action->add($cart, $product, 2, 5);
        $action->add($cart, $product, 1, 1);
        $item = $action->add($cart, $product, 2, 5);

        $this->assertSame(4, $item->quantity);
        $this->assertSame('180.00', (string) $item->total);
        $this->assertSame(1, $cart->items()->where('pack_quantity', 1)->value('quantity'));
        $this->assertSame(2, $cart->items()->count());
    }

    public function test_two_sets_of_one_product_are_two_lines_priced_per_set(): void
    {
        $cart = Cart::factory()->create();
        $product = Product::factory()->create([
            'title' => 'Socks',
            'packs' => [['quantity' => 1, 'price' => 89], ['quantity' => 5, 'price' => 399]],
        ]);

        $action = app(AddOrUpdateCartItemAction::class);
        $single = $action->handle($cart, $product, 7, 1);
        $set = $action->handle($cart, $product, 2, 5);

        $this->assertDatabaseCount('shop_cart_products', 2);
        $this->assertSame('623.00', (string) $single->total);
        $this->assertSame('798.00', (string) $set->total);
        $this->assertSame('399.00', (string) $set->selling_price);
        $this->assertSame(5, $set->pack_quantity);
        // the name tells the sets apart wherever the line is shown
        $this->assertStringStartsWith('Socks — 1 ', $single->product_name);
        $this->assertStringStartsWith('Socks — 5 ', $set->product_name);
    }

    public function test_a_product_sold_by_the_piece_only_keeps_its_bare_title(): void
    {
        $cart = Cart::factory()->create();
        $product = Product::factory()->create(['title' => 'Hoodie', 'price' => 899]);

        $item = app(AddOrUpdateCartItemAction::class)->handle($cart, $product, 1);

        $this->assertSame('Hoodie', $item->product_name);
    }

    public function test_a_set_the_product_is_not_sold_in_is_refused(): void
    {
        $cart = Cart::factory()->create();
        $product = Product::factory()->create(['price' => 10]);

        $this->expectException(InvalidArgumentException::class);

        app(AddOrUpdateCartItemAction::class)->handle($cart, $product, 1, 5);
    }

    public function test_total_is_rounded_to_two_decimals(): void
    {
        $cart = Cart::factory()->create();
        $product = Product::factory()->create(['price' => 10.005]);

        $item = app(AddOrUpdateCartItemAction::class)->handle($cart, $product, 3);

        $this->assertSame('30.02', (string) $item->total);
    }
}
