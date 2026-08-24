<?php

namespace App\Modules\ShopOrders\Queries\Order;

use App\Modules\Core\Dtos\DashboardPeriodData;
use App\Modules\ShopOrders\Dtos\OrderStatsData;
use App\Modules\ShopOrders\Enums\PaymentStatusEnum;
use App\Modules\ShopOrders\Models\Order;
use Carbon\CarbonImmutable;

class GetOrderStatsQuery
{
    /**
     * Sales figures for the selected dashboard period.
     *
     * Two different dates on purpose: how many orders were placed is counted
     * by `created_at`, how much money came in by `payment_date` of paid orders
     * (falling back to `created_at` when `payment_date` was never filled in —
     * see revenueFor()). The Sales widget spells that out in the revenue card
     * description.
     */
    public function handle(DashboardPeriodData $period): OrderStatsData
    {
        $totals = $this->totalsFor($period->from, $period->to);
        $statuses = $this->statusCountsFor($period->from, $period->to);
        $revenue = $this->revenueFor($period->from, $period->to);
        $previousRevenue = $this->revenueFor($period->previousFrom, $period->previousTo);

        $previousOrdersCount = Order::query()
            ->whereBetween('created_at', [$period->previousFrom, $period->previousTo])
            ->count();

        return new OrderStatsData(
            ordersCount: $totals['orders_count'],
            previousOrdersCount: $previousOrdersCount,
            revenue: $revenue['revenue'],
            previousRevenue: $previousRevenue['revenue'],
            // Average of the money that arrived in the window, so both parts of
            // the division come from the same (payment_date) set of orders.
            averageCheck: $revenue['paid_count'] > 0
                ? round($revenue['revenue'] / $revenue['paid_count'], 2)
                : 0.0,
            unpaidCount: $statuses[PaymentStatusEnum::UNPAID->value] ?? 0,
            processingCount: $statuses[PaymentStatusEnum::PROCESSING->value] ?? 0,
            paidCount: $statuses[PaymentStatusEnum::PAID->value] ?? 0,
            discountTotal: $totals['discount_total'],
        );
    }

    /**
     * @return array{orders_count: int, discount_total: float}
     */
    private function totalsFor(CarbonImmutable $from, CarbonImmutable $to): array
    {
        $row = Order::query()
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('COUNT(*) AS orders_count, COALESCE(SUM(discount_amount), 0) AS discount_total')
            ->first();

        return [
            'orders_count' => (int) ($row->orders_count ?? 0),
            'discount_total' => (float) ($row->discount_total ?? 0),
        ];
    }

    /**
     * @return array<string, int>
     */
    private function statusCountsFor(CarbonImmutable $from, CarbonImmutable $to): array
    {
        return Order::query()
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('payment_status, COUNT(*) AS total')
            ->groupBy('payment_status')
            ->pluck('total', 'payment_status')
            ->map(fn ($total): int => (int) $total)
            ->all();
    }

    /**
     * @return array{revenue: float, paid_count: int}
     */
    private function revenueFor(CarbonImmutable $from, CarbonImmutable $to): array
    {
        $row = Order::query()
            ->where('payment_status', PaymentStatusEnum::PAID)
            // payment_date is a free-standing, manually filled field — nothing
            // in this codebase sets it when an order is marked paid. An order
            // with payment_status = paid but payment_date = null (a manually
            // updated or imported order) must not silently vanish from
            // revenue, so it falls back to created_at.
            ->whereRaw('COALESCE(payment_date, created_at) BETWEEN ? AND ?', [$from, $to])
            ->selectRaw('COALESCE(SUM(total_amount), 0) AS revenue, COUNT(*) AS paid_count')
            ->first();

        return [
            'revenue' => (float) ($row->revenue ?? 0),
            'paid_count' => (int) ($row->paid_count ?? 0),
        ];
    }
}
