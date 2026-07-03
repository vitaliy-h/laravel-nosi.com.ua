<?php

namespace Tests\Unit\Shop;

use App\Modules\Orders\Actions\RecalculateOrderTotalAction;
use App\Modules\Orders\Models\Order;
use App\Modules\Orders\Models\OrderProduct;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecalculateOrderTotalActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_sums_item_totals_into_order_total(): void
    {
        $order = Order::factory()->create();
        OrderProduct::factory()->create(['shop_order_id' => $order->id, 'total' => 12.5]);
        OrderProduct::factory()->create(['shop_order_id' => $order->id, 'total' => 7.25]);

        app(RecalculateOrderTotalAction::class)->handle($order);

        $this->assertSame('19.75', (string) $order->fresh()->total_amount);
    }

    public function test_empty_order_totals_zero(): void
    {
        $order = Order::factory()->create(['total_amount' => 42]);

        app(RecalculateOrderTotalAction::class)->handle($order);

        $this->assertSame('0.00', (string) $order->fresh()->total_amount);
    }
}
