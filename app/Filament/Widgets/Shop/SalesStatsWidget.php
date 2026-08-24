<?php

namespace App\Filament\Widgets\Shop;

use App\Filament\Resources\Shop\Cart\CartResource;
use App\Filament\Resources\Shop\Orders\OrderResource;
use App\Filament\Widgets\Concerns\FormatsDashboardStats;
use App\Filament\Widgets\Concerns\InteractsWithDashboardPeriod;
use App\Modules\Core\Support\ModuleGate;
use App\Modules\ShopCart\Queries\Cart\GetCartStatsQuery;
use App\Modules\ShopOrders\Queries\Order\GetOrderStatsQuery;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SalesStatsWidget extends StatsOverviewWidget
{
    use FormatsDashboardStats;
    use InteractsWithDashboardPeriod;

    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = 'full';

    // 8 stats (4 order + 4 cart): let them wrap as two rows of 4 instead of
    // StatsOverviewWidget's default grouping, which would give 3 per row.
    protected int|array|null $columns = 4;

    public static function canView(): bool
    {
        // shop_orders depends on shop_cart in config/modules.php, so this
        // already covers the cart stats appended below.
        return ModuleGate::isEnabled('shop_orders');
    }

    protected function getHeading(): ?string
    {
        return __('admin.dashboard.widgets.sales');
    }

    /**
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        $stats = app(GetOrderStatsQuery::class)->handle($this->period());

        $ordersChange = $this->percentChange($stats->ordersCount, $stats->previousOrdersCount);
        $revenueChange = $this->percentChange($stats->revenue, $stats->previousRevenue);

        return [
            Stat::make(__('admin.dashboard.stats.orders'), $stats->ordersCount)
                ->description($this->trendDescription($ordersChange))
                ->descriptionColor($this->trendColor($ordersChange))
                ->descriptionIcon($this->trendIcon($ordersChange))
                ->icon('heroicon-o-shopping-cart'),

            Stat::make(__('admin.dashboard.stats.payment_status'), $stats->paidCount)
                ->description(__('admin.dashboard.descriptions.payment_breakdown', [
                    'unpaid' => $stats->unpaidCount,
                    'processing' => $stats->processingCount,
                ]))
                ->descriptionColor($this->alertColor($stats->unpaidCount, 'warning'))
                ->icon('heroicon-o-credit-card')
                ->url(OrderResource::getUrl()),

            Stat::make(__('admin.dashboard.stats.revenue'), $this->money($stats->revenue))
                ->description($this->trendDescription($revenueChange))
                ->descriptionColor($this->trendColor($revenueChange))
                ->descriptionIcon($this->trendIcon($revenueChange))
                ->icon('heroicon-o-banknotes'),

            Stat::make(__('admin.dashboard.stats.average_check'), $this->money($stats->averageCheck))
                // Revenue follows payment_date, so the average is over paid
                // orders only — say so instead of letting the numbers look
                // contradictory next to the order count.
                ->description(__('admin.dashboard.descriptions.paid_orders_only'))
                ->icon('heroicon-o-calculator'),

            ...$this->cartStats(),
        ];
    }

    /**
     * @return array<Stat>
     */
    private function cartStats(): array
    {
        $stats = app(GetCartStatsQuery::class)->handle($this->period());

        return [
            Stat::make(__('admin.dashboard.stats.active_carts'), $stats->activeCarts)
                ->description(__('admin.dashboard.descriptions.updated_today'))
                ->icon('heroicon-o-shopping-bag')
                ->url(CartResource::getUrl()),

            Stat::make(__('admin.dashboard.stats.abandoned_carts'), $stats->abandonedCarts)
                ->description(__('admin.dashboard.descriptions.not_updated_today'))
                ->color($this->alertColor($stats->abandonedCarts, 'warning'))
                ->icon('heroicon-o-archive-box-x-mark'),

            Stat::make(__('admin.dashboard.stats.carts_value'), $this->money($stats->cartsValue))
                ->icon('heroicon-o-banknotes'),

            Stat::make(
                __('admin.dashboard.stats.cart_conversion'),
                $stats->conversion === null
                    ? __('admin.dashboard.values.none')
                    : $stats->conversion.'%',
            )
                ->description(__('admin.dashboard.descriptions.carts_in_period', [
                    'carts' => $stats->cartsInPeriod,
                ]))
                ->icon('heroicon-o-arrow-trending-up'),
        ];
    }
}
