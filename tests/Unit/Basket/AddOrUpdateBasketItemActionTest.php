<?php

namespace Tests\Unit\Basket;

use App\Modules\Basket\Actions\AddOrUpdateBasketItemAction;
use App\Modules\Basket\Models\Basket;
use App\Modules\Basket\Models\BasketProduct;
use App\Modules\Shop\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AddOrUpdateBasketItemActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_new_basket_item_with_product_snapshot(): void
    {
        $basket = Basket::factory()->create();
        $product = Product::factory()->create(['title' => 'Widget', 'article' => 'ART-1', 'price' => 10.5]);

        $item = app(AddOrUpdateBasketItemAction::class)->handle($basket, $product, 3);

        $this->assertInstanceOf(BasketProduct::class, $item);
        $this->assertSame($basket->id, $item->basket_id);
        $this->assertSame($product->id, $item->shop_product_id);
        $this->assertSame('Widget', $item->product_name);
        $this->assertSame('ART-1', $item->product_article);
        $this->assertSame('10.50', (string) $item->selling_price);
        $this->assertSame(3, $item->quantity);
        $this->assertSame('31.50', (string) $item->total);
    }

    public function test_updates_existing_item_quantity_instead_of_duplicating(): void
    {
        $basket = Basket::factory()->create();
        $product = Product::factory()->create(['price' => 10]);

        $action = app(AddOrUpdateBasketItemAction::class);
        $action->handle($basket, $product, 1);
        $item = $action->handle($basket, $product, 5);

        $this->assertDatabaseCount('shop_basket_products', 1);
        $this->assertSame(5, $item->quantity);
        $this->assertSame('50.00', (string) $item->total);
    }

    public function test_total_is_rounded_to_two_decimals(): void
    {
        $basket = Basket::factory()->create();
        $product = Product::factory()->create(['price' => 10.005]);

        $item = app(AddOrUpdateBasketItemAction::class)->handle($basket, $product, 3);

        $this->assertSame('30.02', (string) $item->total);
    }
}
