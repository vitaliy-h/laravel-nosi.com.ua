<?php

namespace Tests\Unit\ShopOrder;

use App\Modules\ShopOrder\Enums\PaymentStatusEnum;
use App\Modules\ShopOrder\Models\Order;
use App\Modules\ShopOrder\Services\OrderPermissions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderPermissionsTest extends TestCase
{
    use RefreshDatabase;

    // canPay

    public function test_can_pay_returns_true_when_unpaid(): void
    {
        $order = Order::factory()->create([
            'payment_status' => PaymentStatusEnum::UNPAID,
        ]);

        $this->assertTrue((new OrderPermissions($order))->canPay());
    }

    public function test_can_pay_returns_false_when_processing(): void
    {
        $order = Order::factory()->create([
            'payment_status' => PaymentStatusEnum::PROCESSING,
        ]);

        $this->assertFalse((new OrderPermissions($order))->canPay());
    }

    public function test_can_pay_returns_false_when_paid(): void
    {
        $order = Order::factory()->create([
            'payment_status' => PaymentStatusEnum::PAID,
        ]);

        $this->assertFalse((new OrderPermissions($order))->canPay());
    }

    // Order model accessor

    public function test_order_permissions_accessor_returns_order_permissions_instance(): void
    {
        $order = Order::factory()->create();

        $this->assertInstanceOf(OrderPermissions::class, $order->permissions);
    }
}
