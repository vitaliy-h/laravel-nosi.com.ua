<?php

namespace Tests\Unit\ShopBasket;

use App\Modules\ShopBasket\Actions\RemoveBasketItemAction;
use App\Modules\ShopBasket\Models\Basket;
use App\Modules\ShopBasket\Models\BasketProduct;
use App\Modules\Shop\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RemoveBasketItemActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_removes_item_and_recalculates_total(): void
    {
        $basket = Basket::factory()->create();
        $product = Product::factory()->create();
        $item = BasketProduct::factory()->create([
            'basket_id' => $basket->id,
            'shop_product_id' => $product->id,
            'total' => 20,
        ]);
        BasketProduct::factory()->create(['basket_id' => $basket->id, 'total' => 5]);

        app(RemoveBasketItemAction::class)->handle($basket, $product);

        $this->assertModelMissing($item);
        $this->assertSame('5.00', (string) $basket->fresh()->total_amount);
    }

    public function test_removing_nonexistent_item_is_a_noop(): void
    {
        $basket = Basket::factory()->create();
        $product = Product::factory()->create();
        BasketProduct::factory()->create(['basket_id' => $basket->id, 'total' => 5]);

        app(RemoveBasketItemAction::class)->handle($basket, $product);

        $this->assertSame(1, $basket->items()->count());
        $this->assertSame('5.00', (string) $basket->fresh()->total_amount);
    }
}
