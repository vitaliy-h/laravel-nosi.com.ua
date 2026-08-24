<?php

namespace App\Filament\Dashboard\Queries\Shop;

use App\Filament\Dashboard\Dtos\DashboardPeriodData;
use App\Filament\Dashboard\Dtos\Shop\CartStatsData;
use App\Modules\ShopCart\Models\Cart;
use App\Modules\ShopOrders\Models\Order;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Facades\Cache;

class GetCartStatsQuery
{
    /**
     * A cart untouched for this long counts as abandoned.
     */
    public const ABANDONED_AFTER_HOURS = 24;

    public function handle(DashboardPeriodData $period): CartStatsData
    {
        // config/cache.php sets serializable_classes => false: unserializing
        // any object (including this DTO) from cache storage comes back as
        // __PHP_Incomplete_Class. Cache the plain array instead and rehydrate
        // the DTO on every read.
        $data = Cache::remember(
            "dashboard:carts:{$period->cacheKey()}",
            now()->addMinutes(5),
            fn (): array => $this->build($period)->toArray(),
        );

        return CartStatsData::from($data);
    }

    private function build(DashboardPeriodData $period): CartStatsData
    {
        $threshold = now()->subHours(self::ABANDONED_AFTER_HOURS);

        $activeCarts = Cart::query()
            ->has('items')
            ->where('updated_at', '>=', $threshold)
            ->count();

        $abandonedCarts = Cart::query()
            ->has('items')
            ->where('updated_at', '<', $threshold)
            // Approximation: the schema has no cart_id → order_id link, so a
            // cart counts as converted when the same user placed an order after
            // the cart was last touched. Guest carts (user_id IS NULL) never
            // match and therefore always count as abandoned.
            ->whereNotExists(fn (Builder $query) => $query
                ->selectRaw('1')
                ->from('shop_orders')
                ->whereColumn('shop_orders.user_id', 'shop_carts.user_id')
                ->whereColumn('shop_orders.created_at', '>=', 'shop_carts.updated_at'))
            ->count();

        $cartsValue = (float) Cart::query()
            ->has('items')
            ->sum('total_amount');

        // The conversion rate is the only figure here that follows the period
        // filter — the three counters above are a snapshot of right now.
        $ordersInPeriod = Order::query()
            ->whereBetween('created_at', [$period->from, $period->to])
            ->count();

        // PlaceOrderFromCartAction deletes the cart once it becomes an order,
        // so a cart that converted during this period is no longer in the
        // carts table at all. Add the orders back in — each one was a cart
        // that this query would otherwise miss — or conversion can read well
        // over 100% (few surviving carts, many orders placed from carts that
        // already got deleted).
        $cartsInPeriod = Cart::query()
                ->whereBetween('created_at', [$period->from, $period->to])
                ->count()
            + $ordersInPeriod;

        return new CartStatsData(
            activeCarts: $activeCarts,
            abandonedCarts: $abandonedCarts,
            cartsValue: $cartsValue,
            cartsInPeriod: $cartsInPeriod,
            ordersInPeriod: $ordersInPeriod,
            conversion: $cartsInPeriod > 0
                ? round($ordersInPeriod / $cartsInPeriod * 100, 1)
                : null,
        );
    }
}
