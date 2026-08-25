<?php

namespace App\Filament\Widgets\Shop;

use App\Filament\Resources\Shop\Promocodes\PromocodeResource;
use App\Filament\Dashboard\Concerns\FormatsDashboardStats;
use App\Filament\Dashboard\Concerns\InteractsWithDashboardPeriod;
use App\Modules\Core\Support\ModuleGate;
use App\Filament\Dashboard\Queries\Shop\GetPromocodeStatsQuery;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PromocodeStatsWidget extends StatsOverviewWidget
{
    use FormatsDashboardStats;
    use InteractsWithDashboardPeriod;

    protected static ?int $sort = 8;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return ModuleGate::isEnabled('shop_promocodes');
    }

    protected function getHeading(): ?string
    {
        return __('admin.dashboard.widgets.promocodes');
    }

    /**
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        $stats = app(GetPromocodeStatsQuery::class)->handle($this->period());

        return [
            Stat::make(__('admin.dashboard.stats.active_promocodes'), $stats->activeCount)
                ->description(__('admin.dashboard.descriptions.expired', ['count' => $stats->expiredCount]))
                ->icon('heroicon-o-ticket')
                ->url(PromocodeResource::getUrl()),

            Stat::make(__('admin.dashboard.stats.promocode_uses'), $stats->usesInPeriod)
                ->icon('heroicon-o-check-badge'),

            Stat::make(__('admin.dashboard.stats.discount_given'), $this->money($stats->discountGiven))
                ->description(__('admin.dashboard.descriptions.percent_of_revenue', [
                    'percent' => $stats->discountShare(),
                ]))
                ->icon('heroicon-o-receipt-percent'),

            Stat::make(
                __('admin.dashboard.stats.top_promocode'),
                $stats->topCode ?? __('admin.dashboard.values.none'),
            )
                ->description(__('admin.dashboard.descriptions.uses', ['count' => $stats->topCodeUses]))
                ->icon('heroicon-o-trophy'),
        ];
    }
}
