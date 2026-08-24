<?php

namespace Tests\Unit\Dashboard;

use App\Modules\Core\Dtos\DashboardPeriodData;
use App\Modules\ShopOrders\Models\Order;
use App\Modules\ShopOrders\Queries\Order\GetSalesChartQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class GetSalesChartQueryTest extends TestCase
{
    use RefreshDatabase;

    private GetSalesChartQuery $query;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-08-23 12:00:00');
        // The query caches per period key: without this a neighbouring test
        // would hand this one its numbers.
        Cache::flush();

        $this->query = app(GetSalesChartQuery::class);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_series_has_one_point_per_day_of_the_period(): void
    {
        $chart = $this->query->handle(DashboardPeriodData::fromFilters(['period' => '7']));

        $this->assertCount(7, $chart->labels);
        $this->assertCount(7, $chart->orders);
        $this->assertCount(7, $chart->revenue);
    }

    public function test_days_without_orders_are_present_as_zeros(): void
    {
        $this->order('2026-08-18 10:00:00', 100.00);
        $this->order('2026-08-18 12:00:00', 50.00);
        $this->order('2026-08-23 09:00:00', 25.00);

        $chart = $this->query->handle(DashboardPeriodData::fromFilters(['period' => '7']));

        // 17, 18, 19, 20, 21, 22, 23 August
        $this->assertSame([0, 2, 0, 0, 0, 0, 1], $chart->orders);
        $this->assertSame([0.0, 150.0, 0.0, 0.0, 0.0, 0.0, 25.0], $chart->revenue);
    }

    public function test_orders_outside_the_period_are_ignored(): void
    {
        $this->order('2026-08-01 10:00:00', 999.00);

        $chart = $this->query->handle(DashboardPeriodData::fromFilters(['period' => '7']));

        $this->assertSame(0, array_sum($chart->orders));
        $this->assertSame(0.0, array_sum($chart->revenue));
    }

    public function test_single_day_period_is_bucketed_by_hour(): void
    {
        $this->order('2026-08-23 09:15:00', 10.00);

        $chart = $this->query->handle(DashboardPeriodData::fromFilters(['period' => 'today']));

        $this->assertCount(24, $chart->labels);
        $this->assertSame('09:00', $chart->labels[9]);
        $this->assertSame(1, $chart->orders[9]);
        $this->assertSame(1, array_sum($chart->orders));
    }

    private function order(string $createdAt, float $total): Order
    {
        return Order::factory()->create([
            'total_amount' => $total,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);
    }
}
