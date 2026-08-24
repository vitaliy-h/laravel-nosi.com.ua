<?php

namespace App\Filament\Dashboard\Dtos\Shop;

use Spatie\LaravelData\Data;

/**
 * Series for the dashboard sales chart. All three arrays are the same length:
 * gaps in the data are filled with zeros by GetSalesChartQuery.
 */
class SalesChartData extends Data
{
    /**
     * @param  array<int, string>  $labels
     * @param  array<int, int>  $orders
     * @param  array<int, float>  $revenue
     */
    public function __construct(
        public readonly array $labels,
        public readonly array $orders,
        public readonly array $revenue,
    ) {}
}
