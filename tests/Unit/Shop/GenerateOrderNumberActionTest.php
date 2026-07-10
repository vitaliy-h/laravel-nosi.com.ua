<?php

namespace Tests\Unit\Shop;

use App\Modules\ShopOrders\Actions\GenerateOrderNumberAction;
use App\Modules\ShopOrders\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenerateOrderNumberActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_sets_order_number_equal_to_id_when_missing(): void
    {
        $order = Order::factory()->create();
        $order->forceFill(['order_number' => null])->saveQuietly();

        app(GenerateOrderNumberAction::class)->handle($order);

        $this->assertSame((string) $order->id, (string) $order->fresh()->order_number);
    }

    public function test_does_not_overwrite_existing_order_number(): void
    {
        $order = Order::factory()->create();
        $order->forceFill(['order_number' => 999])->saveQuietly();
        $order->refresh();

        app(GenerateOrderNumberAction::class)->handle($order);

        $this->assertSame('999', (string) $order->fresh()->order_number);
    }
}
