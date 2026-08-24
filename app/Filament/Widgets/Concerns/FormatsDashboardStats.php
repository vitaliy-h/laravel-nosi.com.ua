<?php

namespace App\Filament\Widgets\Concerns;

/**
 * Presentation helpers shared by the dashboard stat widgets: money formatting
 * and the "+X% vs previous period" description with its colour and arrow.
 *
 * Formatting only — anything that touches the database belongs in a Query class.
 */
trait FormatsDashboardStats
{
    protected function money(float $amount): string
    {
        return t('shop.currency').number_format($amount, 2);
    }

    /**
     * Change against the previous window, or null when there is nothing to
     * compare against — an empty previous period would render as "+∞%".
     */
    protected function percentChange(float $current, float $previous): ?float
    {
        if ($previous <= 0.0) {
            return null;
        }

        return round(($current - $previous) / $previous * 100, 1);
    }

    protected function trendDescription(?float $percent): string
    {
        if ($percent === null) {
            return __('admin.dashboard.descriptions.no_change');
        }

        return ($percent > 0 ? '+' : '').$percent.'%';
    }

    protected function trendColor(?float $percent): string
    {
        return match (true) {
            $percent === null, $percent === 0.0 => 'gray',
            $percent > 0 => 'success',
            default => 'danger',
        };
    }

    protected function trendIcon(?float $percent): ?string
    {
        return match (true) {
            $percent === null, $percent === 0.0 => null,
            $percent > 0 => 'heroicon-m-arrow-trending-up',
            default => 'heroicon-m-arrow-trending-down',
        };
    }

    /**
     * Colour for a "needs action" counter: red while there is something to do.
     */
    protected function alertColor(int $count, string $color = 'danger'): string
    {
        return $count > 0 ? $color : 'gray';
    }
}
