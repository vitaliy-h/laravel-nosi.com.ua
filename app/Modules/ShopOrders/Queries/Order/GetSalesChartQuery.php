<?php

namespace App\Modules\ShopOrders\Queries\Order;

use App\Modules\Core\Dtos\DashboardPeriodData;
use App\Modules\Core\Support\DashboardSeries;
use App\Modules\ShopOrders\Dtos\SalesChartData;
use App\Modules\ShopOrders\Models\Order;
use Illuminate\Support\Facades\Cache;

class GetSalesChartQuery
{
    /**
     * Orders and revenue per bucket for the dashboard sales chart.
     *
     * Both series are taken by `created_at` so the two lines describe the same
     * set of orders — unlike the Sales cards, where revenue follows
     * `payment_date`.
     */
    public function handle(DashboardPeriodData $period): SalesChartData
    {
        // config/cache.php sets serializable_classes => false: unserializing
        // any object (including this DTO) from cache storage comes back as
        // __PHP_Incomplete_Class. Cache the plain array instead and rehydrate
        // the DTO on every read.
        $data = Cache::remember(
            "dashboard:sales_chart:{$period->cacheKey()}",
            now()->addMinutes(5),
            fn (): array => $this->build($period)->toArray(),
        );

        return SalesChartData::from($data);
    }

    private function build(DashboardPeriodData $period): SalesChartData
    {
        $rows = Order::query()
            ->whereBetween('created_at', [$period->from, $period->to])
            ->selectRaw(
                DashboardSeries::bucketExpression($period, 'created_at')
                .' AS bucket, COUNT(*) AS orders_count, COALESCE(SUM(total_amount), 0) AS revenue'
            )
            ->groupBy('bucket')
            ->get()
            ->keyBy('bucket');

        $labels = [];
        $orders = [];
        $revenue = [];

        // Empty buckets must stay in the series, otherwise the chart silently
        // squeezes together the weeks without sales.
        foreach (DashboardSeries::buckets($period) as $key => $label) {
            $row = $rows->get($key);

            $labels[] = $label;
            $orders[] = (int) ($row->orders_count ?? 0);
            $revenue[] = round((float) ($row->revenue ?? 0), 2);
        }

        return new SalesChartData($labels, $orders, $revenue);
    }
}
