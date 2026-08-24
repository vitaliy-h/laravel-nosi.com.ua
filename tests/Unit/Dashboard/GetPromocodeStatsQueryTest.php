<?php

namespace Tests\Unit\Dashboard;

use App\Modules\Core\Dtos\DashboardPeriodData;
use App\Modules\ShopCart\Models\Promocode;
use App\Modules\ShopCart\Queries\Promocode\GetPromocodeStatsQuery;
use App\Modules\ShopOrders\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class GetPromocodeStatsQueryTest extends TestCase
{
    use RefreshDatabase;

    private GetPromocodeStatsQuery $query;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-08-23 12:00:00');
        Cache::flush();

        $this->query = app(GetPromocodeStatsQuery::class);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_empty_state_returns_zeros(): void
    {
        $stats = $this->query->handle(DashboardPeriodData::fromFilters(['period' => '30']));

        $this->assertSame(0, $stats->activeCount);
        $this->assertSame(0, $stats->usesInPeriod);
        $this->assertSame(0.0, $stats->discountGiven);
        $this->assertNull($stats->topCode);
    }

    public function test_active_and_expired_counts_follow_the_promocode_scope(): void
    {
        Promocode::factory()->create();
        Promocode::factory()->expired()->create();
        Promocode::factory()->invisible()->create();

        $stats = $this->query->handle(DashboardPeriodData::fromFilters(['period' => '30']));

        $this->assertSame(1, $stats->activeCount);
        $this->assertSame(1, $stats->expiredCount);
    }

    public function test_uses_and_discount_are_counted_from_orders_in_the_period(): void
    {
        $this->orderWithPromocode('SAVE10', 15.00, '2026-08-20 10:00:00');
        $this->orderWithPromocode('SAVE10', 10.00, '2026-08-21 10:00:00');
        // Outside the period.
        $this->orderWithPromocode('SAVE10', 999.00, '2026-07-01 10:00:00');
        // No promocode: not counted as a use.
        Order::factory()->create([
            'created_at' => '2026-08-20 10:00:00',
            'updated_at' => '2026-08-20 10:00:00',
        ]);

        $stats = $this->query->handle(DashboardPeriodData::fromFilters(['period' => '30']));

        $this->assertSame(2, $stats->usesInPeriod);
        $this->assertSame(25.0, $stats->discountGiven);
    }

    public function test_top_promocode_is_the_most_used_code_in_the_period(): void
    {
        $this->orderWithPromocode('SAVE10', 10.00, '2026-08-20 10:00:00');
        $this->orderWithPromocode('SAVE10', 10.00, '2026-08-21 10:00:00');
        $this->orderWithPromocode('WELCOME', 5.00, '2026-08-21 11:00:00');

        $stats = $this->query->handle(DashboardPeriodData::fromFilters(['period' => '30']));

        $this->assertSame('SAVE10', $stats->topCode);
        $this->assertSame(2, $stats->topCodeUses);
    }

    private function orderWithPromocode(string $code, float $discount, string $createdAt): Order
    {
        return Order::factory()->create([
            'promocode_code' => $code,
            'discount_amount' => $discount,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);
    }
}
