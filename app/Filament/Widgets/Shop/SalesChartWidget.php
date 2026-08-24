<?php

namespace App\Filament\Widgets\Shop;

use App\Filament\Widgets\Concerns\InteractsWithDashboardPeriod;
use App\Modules\Core\Support\ModuleGate;
use App\Modules\ShopOrders\Queries\Order\GetSalesChartQuery;
use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\Support\Htmlable;

class SalesChartWidget extends ChartWidget
{
    use InteractsWithDashboardPeriod;

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected ?string $maxHeight = '300px';

    public static function canView(): bool
    {
        return ModuleGate::isEnabled('shop_orders');
    }

    public function getHeading(): string|Htmlable|null
    {
        return __('admin.dashboard.widgets.sales_chart');
    }

    protected function getType(): string
    {
        return 'line';
    }

    /**
     * @return array<string, mixed>
     */
    protected function getData(): array
    {
        $chart = app(GetSalesChartQuery::class)->handle($this->period());

        return [
            'datasets' => [
                [
                    'label' => __('admin.dashboard.stats.orders'),
                    'data' => $chart->orders,
                    'yAxisID' => 'orders',
                    'borderColor' => '#0ea5e9',
                    'backgroundColor' => 'rgba(14, 165, 233, 0.1)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
                [
                    'label' => __('admin.dashboard.stats.revenue'),
                    'data' => $chart->revenue,
                    'yAxisID' => 'revenue',
                    'borderColor' => '#22c55e',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                    'fill' => false,
                    'tension' => 0.3,
                ],
            ],
            'labels' => $chart->labels,
        ];
    }

    /**
     * Orders and revenue live on very different scales — give the money series
     * its own axis on the right.
     *
     * @return array<string, mixed>
     */
    protected function getOptions(): array
    {
        return [
            'scales' => [
                'orders' => [
                    'type' => 'linear',
                    'position' => 'left',
                    'beginAtZero' => true,
                    'ticks' => ['precision' => 0],
                ],
                'revenue' => [
                    'type' => 'linear',
                    'position' => 'right',
                    'beginAtZero' => true,
                    'grid' => ['drawOnChartArea' => false],
                ],
            ],
        ];
    }
}
