<?php

namespace Tests\Unit\Shop;

use App\Modules\Orders\Enums\PaymentStatusEnum;
use App\Modules\Orders\Models\Order;
use App\Modules\Orders\Services\OrderActions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderActionsTest extends TestCase
{
    use RefreshDatabase;

    // canPay

    public function test_canPay_returns_true_when_unpaid(): void
    {
        $order = Order::factory()->create([
            'payment_status' => PaymentStatusEnum::UNPAID,
        ]);

        $this->assertTrue((new OrderActions($order))->canPay());
    }

    public function test_canPay_returns_false_when_processing(): void
    {
        $order = Order::factory()->create([
            'payment_status' => PaymentStatusEnum::PROCESSING,
        ]);

        $this->assertFalse((new OrderActions($order))->canPay());
    }

    public function test_canPay_returns_false_when_paid(): void
    {
        $order = Order::factory()->create([
            'payment_status' => PaymentStatusEnum::PAID,
        ]);

        $this->assertFalse((new OrderActions($order))->canPay());
    }

    // Order model accessor

    public function test_order_actions_accessor_returns_order_actions_instance(): void
    {
        $order = Order::factory()->create();

        $this->assertInstanceOf(OrderActions::class, $order->actions);
    }
}
