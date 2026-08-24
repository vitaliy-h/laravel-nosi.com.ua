<?php

namespace App\Modules\Shop\Queries\Product;

use App\Modules\Core\Dtos\DashboardPeriodData;
use App\Modules\Shop\Models\Product;
use App\Modules\ShopOrders\Models\OrderProduct;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class GetTopProductsQuery
{
    public const DEFAULT_LIMIT = 5;

    /**
     * Best selling products of the period, as a Builder the TableWidget can
     * render.
     *
     * A Builder cannot be cached, so the heavy part — the grouped aggregate
     * over the order lines — is cached separately by aggregates() and the
     * Builder is a plain `whereIn` over the resulting ids.
     */
    public function handle(DashboardPeriodData $period, int $limit = self::DEFAULT_LIMIT): Builder
    {
        $ids = array_keys($this->aggregates($period, $limit));

        $query = Product::query()->whereIn('shop_products.id', $ids);

        if ($ids !== []) {
            // Keep the "best selling first" order of the aggregate.
            $query->orderByRaw('FIELD(shop_products.id, '.implode(',', $ids).')');
        }

        return $query;
    }

    /**
     * Units sold and revenue per product id, ordered by units sold desc.
     *
     * Cached: the widget asks for it twice per render (once to build the
     * Builder, once to fill the `sold` / `revenue` columns).
     *
     * @return array<int, array{sold: int, revenue: float}>
     */
    public function aggregates(DashboardPeriodData $period, int $limit = self::DEFAULT_LIMIT): array
    {
        return Cache::remember(
            "dashboard:top_products:{$period->cacheKey()}:{$limit}",
            now()->addMinutes(5),
            fn (): array => OrderProduct::query()
                ->join('shop_orders', 'shop_orders.id', '=', 'shop_order_products.shop_order_id')
                ->whereBetween('shop_orders.created_at', [$period->from, $period->to])
                ->groupBy('shop_order_products.shop_product_id')
                ->orderByDesc('sold')
                ->limit($limit)
                ->get([
                    'shop_order_products.shop_product_id',
                    DB::raw('COALESCE(SUM(shop_order_products.quantity), 0) AS sold'),
                    DB::raw('COALESCE(SUM(shop_order_products.total), 0) AS revenue'),
                ])
                ->mapWithKeys(fn ($row): array => [
                    (int) $row->shop_product_id => [
                        'sold' => (int) $row->sold,
                        'revenue' => (float) $row->revenue,
                    ],
                ])
                ->all(),
        );
    }
}
