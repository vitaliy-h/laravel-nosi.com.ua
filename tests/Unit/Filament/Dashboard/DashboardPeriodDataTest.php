<?php

namespace Tests\Unit\Filament\Dashboard;

use App\Filament\Dashboard\Dtos\DashboardPeriodData;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class DashboardPeriodDataTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Window boundaries must not drift when the suite runs around midnight.
        Carbon::setTestNow('2026-08-23 15:30:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_today_period_covers_the_current_day(): void
    {
        $period = DashboardPeriodData::fromFilters(['period' => 'today']);

        $this->assertSame('2026-08-23 00:00:00', $period->from->toDateTimeString());
        $this->assertSame('2026-08-23 23:59:59', $period->to->toDateTimeString());
        $this->assertSame('today', $period->key);
        $this->assertSame(1, $period->days());
        $this->assertTrue($period->isSingleDay());
    }

    public function test_fixed_periods_include_today_as_the_last_day(): void
    {
        foreach ([7 => '2026-08-17', 30 => '2026-07-25', 90 => '2026-05-26'] as $days => $expectedFrom) {
            $period = DashboardPeriodData::fromFilters(['period' => (string) $days]);

            $this->assertSame($expectedFrom.' 00:00:00', $period->from->toDateTimeString());
            $this->assertSame('2026-08-23 23:59:59', $period->to->toDateTimeString());
            $this->assertSame($days, $period->days());
        }
    }

    public function test_previous_window_has_the_same_length_and_ends_before_the_current_one(): void
    {
        $period = DashboardPeriodData::fromFilters(['period' => '7']);

        $this->assertSame('2026-08-10 00:00:00', $period->previousFrom->toDateTimeString());
        $this->assertSame('2026-08-16 23:59:59', $period->previousTo->toDateTimeString());
        $this->assertTrue($period->previousTo->lessThan($period->from));
    }

    public function test_custom_period_uses_the_given_dates(): void
    {
        $period = DashboardPeriodData::fromFilters([
            'period' => 'custom',
            'from' => '2026-08-01',
            'to' => '2026-08-10',
        ]);

        $this->assertSame('custom', $period->key);
        $this->assertSame('2026-08-01 00:00:00', $period->from->toDateTimeString());
        $this->assertSame('2026-08-10 23:59:59', $period->to->toDateTimeString());
        $this->assertSame(10, $period->days());
    }

    public function test_custom_period_with_reversed_range_swaps_the_bounds(): void
    {
        $period = DashboardPeriodData::fromFilters([
            'period' => 'custom',
            'from' => '2026-08-10',
            'to' => '2026-08-01',
        ]);

        $this->assertSame('2026-08-01 00:00:00', $period->from->toDateTimeString());
        $this->assertSame('2026-08-10 23:59:59', $period->to->toDateTimeString());
    }

    public function test_custom_period_without_dates_falls_back_to_thirty_days(): void
    {
        $period = DashboardPeriodData::fromFilters(['period' => 'custom']);

        $this->assertSame('2026-07-25 00:00:00', $period->from->toDateTimeString());
        $this->assertSame('2026-08-23 23:59:59', $period->to->toDateTimeString());
    }

    /**
     * @return array<string, array{0: array<string, mixed>|null}>
     */
    public static function garbageFiltersProvider(): array
    {
        return [
            'null filters' => [null],
            'empty filters' => [[]],
            'empty period' => [['period' => '']],
            'unknown period' => [['period' => 'last-century']],
            'array period' => [['period' => ['30']]],
            'numeric junk' => [['period' => '999']],
        ];
    }

    /**
     * @param  array<string, mixed>|null  $filters
     */
    #[DataProvider('garbageFiltersProvider')]
    public function test_broken_filters_fall_back_to_the_default_period(?array $filters): void
    {
        $period = DashboardPeriodData::fromFilters($filters);

        $this->assertSame('30', $period->key);
        $this->assertSame('2026-07-25 00:00:00', $period->from->toDateTimeString());
        $this->assertSame('2026-08-23 23:59:59', $period->to->toDateTimeString());
        $this->assertSame(30, $period->days());
    }

    public function test_custom_period_ignores_an_unparsable_date(): void
    {
        $period = DashboardPeriodData::fromFilters([
            'period' => 'custom',
            'from' => 'not-a-date',
            'to' => '2026-08-10',
        ]);

        $this->assertSame('2026-07-25 00:00:00', $period->from->toDateTimeString());
        $this->assertSame('2026-08-10 23:59:59', $period->to->toDateTimeString());
    }

    public function test_cache_key_contains_the_period_and_both_bounds(): void
    {
        $period = DashboardPeriodData::fromFilters(['period' => '30']);

        $this->assertSame('30:2026-07-25:2026-08-23', $period->cacheKey());
    }
}
