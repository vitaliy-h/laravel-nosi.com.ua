<?php

namespace Tests\Unit\ShopOrders;

use App\Modules\ShopOrders\Actions\RecalculateOrderTotalAction;
use App\Modules\ShopOrders\Models\Order;
use App\Modules\ShopOrders\Models\OrderProduct;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecalculateOrderTotalActionTest extends TestCase
{
    use RefreshDatabase;

    private RecalculateOrderTotalAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(RecalculateOrderTotalAction::class);
    }

    public function test_sums_item_totals_into_order_total(): void
    {
        $order = Order::factory()->create();
        OrderProduct::factory()->create(['shop_order_id' => $order->id, 'total' => 12.5]);
        OrderProduct::factory()->create(['shop_order_id' => $order->id, 'total' => 7.25]);

        $this->action->handle($order);

        $this->assertSame('19.75', (string) $order->fresh()->total_amount);
    }

    public function test_empty_order_totals_zero(): void
    {
        $order = Order::factory()->create(['total_amount' => 42]);

        $this->action->handle($order);

        $this->assertSame('0.00', (string) $order->fresh()->total_amount);
    }

    public function test_discount_is_subtracted_from_subtotal(): void
    {
        $order = $this->orderWithItemTotal(200, ['discount_amount' => 25]);

        $this->action->handle($order);

        $order->refresh();

        $this->assertSame('200.00', $order->subtotal_amount);
        $this->assertSame('25.00', $order->discount_amount);
        $this->assertSame('175.00', $order->total_amount);
    }

    public function test_discount_larger_than_subtotal_is_clamped(): void
    {
        $order = $this->orderWithItemTotal(40, ['discount_amount' => 500]);

        $this->action->handle($order);

        $order->refresh();

        $this->assertSame('40.00', $order->discount_amount);
        $this->assertSame('0.00', $order->total_amount);
    }

    public function test_promocode_is_not_revalidated_on_the_order(): void
    {
        // The order keeps its frozen discount even for a code that no longer
        // validates in the cart.
        $order = $this->orderWithItemTotal(100, [
            'promocode_code' => 'DELETED',
            'discount_amount' => 10,
        ]);

        $this->action->handle($order);

        $order->refresh();

        $this->assertSame('DELETED', $order->promocode_code);
        $this->assertSame('90.00', $order->total_amount);
    }

    /**
     * @param  array<string, mixed>  $orderAttributes
     */
    private function orderWithItemTotal(float $total, array $orderAttributes = []): Order
    {
        $order = Order::factory()->create($orderAttributes);

        OrderProduct::factory()->create([
            'shop_order_id' => $order->id,
            'selling_price' => $total,
            'quantity' => 1,
            'total' => $total,
        ]);

        return $order->refresh();
    }
}
