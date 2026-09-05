<?php

namespace Tests\Unit\ShopOrder;

use App\Modules\ShopOrder\Models\Order;
use App\Modules\ShopOrder\Models\OrderProduct;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderProductObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_a_line_recalculates_the_order_total(): void
    {
        $order = Order::factory()->create();

        $this->lineFor($order, 30);

        $this->assertSame('30.00', $order->fresh()->total_amount);
    }

    public function test_deleting_a_line_recalculates_the_order_total(): void
    {
        $order = Order::factory()->create();
        $kept = $this->lineFor($order, 30);
        $removed = $this->lineFor($order, 12.5);

        $this->assertSame('42.50', $order->fresh()->total_amount);

        $removed->delete();

        $this->assertSame('30.00', $order->fresh()->total_amount);
        $this->assertSame('30.00', $order->fresh()->subtotal_amount);
        $this->assertModelExists($kept);
    }

    public function test_deleting_the_last_line_zeroes_the_order_total(): void
    {
        $order = Order::factory()->create();
        $line = $this->lineFor($order, 99.99);

        $line->delete();

        $this->assertSame('0.00', $order->fresh()->total_amount);
    }

    public function test_deleting_a_line_keeps_the_frozen_discount_applied(): void
    {
        $order = Order::factory()->create(['discount_amount' => 10]);
        $this->lineFor($order, 100);
        $removed = $this->lineFor($order, 40);

        $removed->delete();

        $order->refresh();

        $this->assertSame('100.00', $order->subtotal_amount);
        $this->assertSame('10.00', $order->discount_amount);
        $this->assertSame('90.00', $order->total_amount);
    }

    private function lineFor(Order $order, float $total): OrderProduct
    {
        return OrderProduct::factory()->create([
            'shop_order_id' => $order->id,
            'selling_price' => $total,
            'quantity'      => 1,
            'total'         => $total,
        ]);
    }
}
