<?php

namespace Tests\Unit\Basket;

use App\Modules\Basket\Actions\RecalculateBasketTotalAction;
use App\Modules\Basket\Models\Basket;
use App\Modules\Basket\Models\BasketProduct;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecalculateBasketTotalActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_sums_item_totals_into_basket_total(): void
    {
        $basket = Basket::factory()->create();
        BasketProduct::factory()->create(['basket_id' => $basket->id, 'total' => 10.5]);
        BasketProduct::factory()->create(['basket_id' => $basket->id, 'total' => 5.25]);

        app(RecalculateBasketTotalAction::class)->handle($basket);

        $this->assertSame('15.75', (string) $basket->fresh()->total_amount);
    }

    public function test_empty_basket_totals_zero(): void
    {
        $basket = Basket::factory()->create(['total_amount' => 99]);

        app(RecalculateBasketTotalAction::class)->handle($basket);

        $this->assertSame('0.00', (string) $basket->fresh()->total_amount);
    }
}
