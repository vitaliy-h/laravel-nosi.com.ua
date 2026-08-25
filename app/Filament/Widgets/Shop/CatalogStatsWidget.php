<?php

namespace App\Filament\Widgets\Shop;

use App\Filament\Resources\Shop\Brands\BrandResource;
use App\Filament\Resources\Shop\Categories\CategoryResource;
use App\Filament\Resources\Shop\Products\ProductResource;
use App\Filament\Dashboard\Concerns\FormatsDashboardStats;
use App\Modules\Core\Support\ModuleGate;
use App\Filament\Dashboard\Queries\Shop\GetCatalogStatsQuery;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Catalog snapshot. Deliberately period-independent, so it does not use
 * InteractsWithDashboardPeriod.
 */
class CatalogStatsWidget extends StatsOverviewWidget
{
    use FormatsDashboardStats;

    protected static ?int $sort = 6;

    // Sits beside TopProductsWidget (sort 5, also columnSpan 1) in the
    // dashboard's 2-column grid instead of taking a full-width row above it.
    // Higher sort than TopProductsWidget so products land on the left.
    protected int|string|array $columnSpan = 1;

    // 2 per row instead of StatsOverviewWidget's default (3), since the
    // widget itself is now only half the page width.
    protected int|array|null $columns = 2;

    public static function canView(): bool
    {
        return ModuleGate::isEnabled('shop');
    }

    // No heading: it sits beside TopProductsWidget as a plain block of 4
    // cards, not a titled "Catalog" section.

    /**
     * @return array<Stat>
     */
    protected function getStats(): array
    {
        $stats = app(GetCatalogStatsQuery::class)->handle();

        // A null brandsTotal means shop_brands is off: BrandResource is not
        // registered then, so the card cannot even build its link.
        return array_values(array_filter([
            Stat::make(__('admin.dashboard.stats.products'), $stats->productsTotal)
                ->description(__('admin.dashboard.descriptions.hidden', ['count' => $stats->productsHidden]))
                ->icon('heroicon-o-cube')
                ->url(ProductResource::getUrl()),

            Stat::make(__('admin.dashboard.stats.categories'), $stats->categoriesTotal)
                ->description(__('admin.dashboard.descriptions.hidden', ['count' => $stats->categoriesHidden]))
                ->icon('heroicon-o-squares-2x2')
                ->url(CategoryResource::getUrl()),

            $stats->brandsTotal === null ? null : Stat::make(__('admin.dashboard.stats.brands'), $stats->brandsTotal)
                ->icon('heroicon-o-tag')
                ->url(BrandResource::getUrl()),

            Stat::make(__('admin.dashboard.stats.incomplete_products'), $stats->incompleteProducts)
                ->description(__('admin.dashboard.descriptions.no_image_or_price'))
                ->color($this->alertColor($stats->incompleteProducts, 'warning'))
                ->icon('heroicon-o-exclamation-triangle')
                ->url(ProductResource::getUrl(parameters: [
                    'filters' => ['incomplete' => ['isActive' => '1']],
                ])),
        ]));
    }
}
