<?php

namespace Tests\Unit\ShopCart\ViewModels;

use App\Modules\Core\Support\Money;
use App\Modules\Shop\Models\Product;
use App\Modules\ShopCart\Models\Cart;
use App\Modules\ShopCart\Models\CartProduct;
use App\Modules\ShopCart\ViewModels\CartViewModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartViewModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_empty_cart_reports_itself_empty_and_without_discount(): void
    {
        $cart = Cart::factory()->create(['discount_amount' => 0]);

        $vm = CartViewModel::from($cart->load('items.product'));

        $this->assertTrue($vm->is_empty);
        $this->assertFalse($vm->has_discount);
        $this->assertSame(Money::from($cart->total_amount)->format(), $vm->total_amount_formatted);
    }

    public function test_a_discounted_cart_exposes_the_promocode_and_the_formatted_amounts(): void
    {
        $cart = Cart::factory()->create([
            'subtotal_amount' => 100,
            'discount_amount' => 10,
            'total_amount'    => 90,
            'promocode_code'  => 'SAVE10',
        ]);

        $vm = CartViewModel::from($cart->load('items.product'));

        $this->assertTrue($vm->has_discount);
        $this->assertSame('SAVE10', $vm->promocode_code);
        $this->assertSame(Money::from(90)->format(), $vm->total_amount_formatted);
        $this->assertSame(Money::from(10)->format(), $vm->discount_amount_formatted);
    }

    /**
     * A line keeps the name it was added under: renaming the product later must
     * not rewrite an existing cart.
     */
    public function test_a_line_shows_its_own_snapshot_of_the_product_name(): void
    {
        $cart = Cart::factory()->create();
        $product = Product::factory()->create(['title' => 'Current Title']);
        CartProduct::factory()->create([
            'cart_id'         => $cart->id,
            'shop_product_id' => $product->id,
            'product_name'    => 'Name When Added',
        ]);

        $item = CartViewModel::from($cart->load('items.product'))->items->first();

        $this->assertSame('Name When Added', $item->name);
        $this->assertSame($product->id, $item->product_id);
    }
}
