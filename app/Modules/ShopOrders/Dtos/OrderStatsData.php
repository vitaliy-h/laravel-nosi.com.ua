<?php

namespace App\Modules\ShopOrders\Dtos;

use Spatie\LaravelData\Data;

/**
 * Order/revenue aggregates for the dashboard Sales widget.
 *
 * Order counts are taken by `created_at` (when the order was placed) and the
 * revenue by `payment_date` (when the money actually arrived) — deliberately
 * two different dates, see GetOrderStatsQuery.
 */
class OrderStatsData extends Data
{
    public function __construct(
        public readonly int $ordersCount,
        public readonly int $previousOrdersCount,
        public readonly float $revenue,
        public readonly float $previousRevenue,
        public readonly float $averageCheck,
        public readonly int $unpaidCount,
        public readonly int $processingCount,
        public readonly int $paidCount,
        public readonly float $discountTotal,
    ) {}
}
