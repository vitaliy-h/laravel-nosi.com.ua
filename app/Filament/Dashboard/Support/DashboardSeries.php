<?php

namespace App\Filament\Dashboard\Support;

use App\Filament\Dashboard\Dtos\DashboardPeriodData;

/**
 * Time buckets for the dashboard charts and sparklines.
 *
 * Keeps the SQL grouping expression and the PHP calendar walk in one place, so
 * the keys a query groups by always match the keys it later fills the gaps
 * with. A period covering a single day is bucketed by hour, anything longer by
 * day.
 */
class DashboardSeries
{
    /**
     * Grouping expression for the given datetime column. The column name comes
     * from the calling query, never from user input.
     */
    public static function bucketExpression(DashboardPeriodData $period, string $column): string
    {
        return $period->isSingleDay()
            ? "DATE_FORMAT({$column}, '%Y-%m-%d %H')"
            : "DATE({$column})";
    }

    /**
     * Every bucket of the period in order: SQL key => chart label.
     *
     * @return array<string, string>
     */
    public static function buckets(DashboardPeriodData $period): array
    {
        return $period->isSingleDay()
            ? self::hours($period)
            : self::days($period);
    }

    /**
     * @return array<string, string>
     */
    private static function hours(DashboardPeriodData $period): array
    {
        $buckets = [];
        $start = $period->from->startOfDay();

        for ($hour = 0; $hour < 24; $hour++) {
            $point = $start->addHours($hour);
            $buckets[$point->format('Y-m-d H')] = $point->format('H:00');
        }

        return $buckets;
    }

    /**
     * @return array<string, string>
     */
    private static function days(DashboardPeriodData $period): array
    {
        $buckets = [];
        $cursor = $period->from->startOfDay();
        $last = $period->to->startOfDay();

        while ($cursor->lessThanOrEqualTo($last)) {
            $buckets[$cursor->format('Y-m-d')] = $cursor->format('d M');
            $cursor = $cursor->addDay();
        }

        return $buckets;
    }
}
