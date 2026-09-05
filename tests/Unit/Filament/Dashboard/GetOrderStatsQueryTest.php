<?php

namespace Tests\Unit\Filament\Dashboard;

use App\Filament\Dashboard\Dtos\DashboardPeriodData;
use App\Filament\Dashboard\Queries\Shop\GetOrderStatsQuery;
use App\Modules\ShopOrder\Enums\PaymentStatusEnum;
use App\Modules\ShopOrder\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class GetOrderStatsQueryTest extends TestCase
{
    use RefreshDatabase;

    private GetOrderStatsQuery $query;

    protected function setUp(): void
    {
        parent::setUp();

        $this->skipUnlessModules('shop_cart');

        Carbon::setTestNow('2026-08-23 12:00:00');

        $this->query = app(GetOrderStatsQuery::class);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_empty_period_returns_zeros(): void
    {
        $stats = $this->query->handle(DashboardPeriodData::fromFilters(['period' => '30']));

        $this->assertSame(0, $stats->ordersCount);
        $this->assertSame(0.0, $stats->revenue);
        $this->assertSame(0.0, $stats->averageCheck);
        $this->assertSame(0, $stats->paidCount);
    }

    public function test_revenue_counts_paid_orders_only(): void
    {
        $this->order(PaymentStatusEnum::PAID, 100.00, '2026-08-20 10:00:00', '2026-08-20 10:00:00');
        $this->order(PaymentStatusEnum::UNPAID, 500.00, '2026-08-20 11:00:00');
        $this->order(PaymentStatusEnum::PROCESSING, 700.00, '2026-08-20 12:00:00');

        $stats = $this->query->handle(DashboardPeriodData::fromFilters(['period' => '30']));

        $this->assertSame(3, $stats->ordersCount);
        $this->assertSame(100.0, $stats->revenue);
        $this->assertSame(1, $stats->paidCount);
        $this->assertSame(1, $stats->unpaidCount);
        $this->assertSame(1, $stats->processingCount);
    }

    public function test_revenue_follows_the_payment_date_not_the_creation_date(): void
    {
        // Placed before the window, paid inside it.
        $this->order(PaymentStatusEnum::PAID, 250.00, '2026-06-01 10:00:00', '2026-08-20 10:00:00');

        $stats = $this->query->handle(DashboardPeriodData::fromFilters(['period' => '30']));

        $this->assertSame(0, $stats->ordersCount);
        $this->assertSame(250.0, $stats->revenue);
    }

    public function test_average_check_divides_revenue_by_paid_orders(): void
    {
        $this->order(PaymentStatusEnum::PAID, 100.00, '2026-08-20 10:00:00', '2026-08-20 10:00:00');
        $this->order(PaymentStatusEnum::PAID, 300.00, '2026-08-21 10:00:00', '2026-08-21 10:00:00');

        $stats = $this->query->handle(DashboardPeriodData::fromFilters(['period' => '30']));

        $this->assertSame(400.0, $stats->revenue);
        $this->assertSame(200.0, $stats->averageCheck);
    }

    public function test_paid_order_without_a_payment_date_still_counts_as_revenue(): void
    {
        // payment_date is a free-standing manual field: nothing in this
        // codebase sets it when an order is marked paid, so a paid order can
        // easily have payment_date = null. It must fall back to created_at
        // rather than silently vanish from revenue.
        $this->order(PaymentStatusEnum::PAID, 100.00, '2026-08-20 10:00:00');
        $this->order(PaymentStatusEnum::PAID, 300.00, '2026-08-21 10:00:00');

        $stats = $this->query->handle(DashboardPeriodData::fromFilters(['period' => '30']));

        $this->assertSame(400.0, $stats->revenue);
        $this->assertSame(2, $stats->paidCount);
        $this->assertSame(200.0, $stats->averageCheck);
    }

    public function test_previous_window_does_not_leak_into_the_current_one(): void
    {
        // 7-day window is 2026-08-17 → 2026-08-23, previous is 2026-08-10 → 2026-08-16.
        $this->order(PaymentStatusEnum::PAID, 100.00, '2026-08-20 10:00:00', '2026-08-20 10:00:00');
        $this->order(PaymentStatusEnum::PAID, 40.00, '2026-08-12 10:00:00', '2026-08-12 10:00:00');

        $stats = $this->query->handle(DashboardPeriodData::fromFilters(['period' => '7']));

        $this->assertSame(1, $stats->ordersCount);
        $this->assertSame(100.0, $stats->revenue);
        $this->assertSame(1, $stats->previousOrdersCount);
        $this->assertSame(40.0, $stats->previousRevenue);
    }

    public function test_discount_total_sums_the_window(): void
    {
        $this->order(PaymentStatusEnum::UNPAID, 100.00, '2026-08-20 10:00:00', discount: 15.00);
        $this->order(PaymentStatusEnum::UNPAID, 100.00, '2026-08-21 10:00:00', discount: 5.50);

        $stats = $this->query->handle(DashboardPeriodData::fromFilters(['period' => '30']));

        $this->assertSame(20.5, $stats->discountTotal);
    }

    private function order(
        PaymentStatusEnum $status,
        float $total,
        string $createdAt,
        ?string $paymentDate = null,
        float $discount = 0.0,
    ): Order {
        return Order::factory()->create([
            'payment_status'  => $status,
            'total_amount'    => $total,
            'discount_amount' => $discount,
            'created_at'      => $createdAt,
            'updated_at'      => $createdAt,
            'payment_date'    => $paymentDate,
        ]);
    }
}
