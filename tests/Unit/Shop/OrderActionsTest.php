<?php

namespace Tests\Unit\Shop;

use App\Modules\Shop\Enums\PaymentStatusEnum;
use App\Modules\Shop\Enums\PaymentTypeEnum;
use App\Modules\Shop\Models\Order;
use App\Modules\Shop\Services\OrderActions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderActionsTest extends TestCase
{
    use RefreshDatabase;

    // canPay

    public function test_canPay_returns_true_when_unpaid_and_online_payment(): void
    {
        $order = Order::factory()->create([
            'payment_status' => PaymentStatusEnum::UNPAID,
            'payment_type'   => PaymentTypeEnum::ONLINE_PAYMENT,
        ]);

        $this->assertTrue((new OrderActions($order))->canPay());
    }

    public function test_canPay_returns_false_when_unpaid_and_no_payment_type(): void
    {
        $order = Order::factory()->create([
            'payment_status' => PaymentStatusEnum::UNPAID,
            'payment_type'   => PaymentTypeEnum::NONE,
        ]);

        $this->assertFalse((new OrderActions($order))->canPay());
    }

    public function test_canPay_returns_false_when_unpaid_and_manual_payment(): void
    {
        $order = Order::factory()->create([
            'payment_status' => PaymentStatusEnum::UNPAID,
            'payment_type'   => PaymentTypeEnum::MANUAL_PAYMENT,
        ]);

        $this->assertFalse((new OrderActions($order))->canPay());
    }

    public function test_canPay_returns_false_when_processing(): void
    {
        $order = Order::factory()->create([
            'payment_status' => PaymentStatusEnum::PROCESSING,
            'payment_type'   => PaymentTypeEnum::ONLINE_PAYMENT,
        ]);

        $this->assertFalse((new OrderActions($order))->canPay());
    }

    public function test_canPay_returns_false_when_paid(): void
    {
        $order = Order::factory()->create([
            'payment_status' => PaymentStatusEnum::PAID,
            'payment_type'   => PaymentTypeEnum::ONLINE_PAYMENT,
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
