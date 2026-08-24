<?php

namespace App\Filament\Dashboard\Concerns;

use App\Filament\Dashboard\Dtos\DashboardPeriodData;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

/**
 * Gives a widget the dashboard period filter as a resolved DTO.
 *
 * Only widgets that actually recalculate per period should use it — the
 * `$pageFilters` property is reactive, so pulling it in makes the widget
 * re-render on every filter change.
 */
trait InteractsWithDashboardPeriod
{
    use InteractsWithPageFilters;

    protected function period(): DashboardPeriodData
    {
        return DashboardPeriodData::fromFilters($this->pageFilters);
    }
}
