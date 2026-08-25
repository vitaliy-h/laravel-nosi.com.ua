<?php

namespace App\Filament\Widgets\Shop;

use App\Filament\Resources\Shop\Products\ProductResource;
use App\Filament\Dashboard\Concerns\InteractsWithDashboardPeriod;
use App\Modules\Core\Support\ModuleGate;
use App\Modules\Shop\Models\Product;
use App\Filament\Dashboard\Queries\Shop\GetTopProductsQuery;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class TopProductsWidget extends TableWidget
{
    use InteractsWithDashboardPeriod;

    // Left of CatalogStatsWidget (sort 6) in the dashboard's 2-column grid.
    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 1;

    public static function canView(): bool
    {
        return ModuleGate::isEnabled('shop_cart');
    }

    public function table(Table $table): Table
    {
        $query = app(GetTopProductsQuery::class);
        $period = $this->period();
        // Cached by the query class, so asking for it here costs nothing extra.
        $aggregates = $query->aggregates($period);

        return $table
            ->query($query->handle($period))
            ->heading(null)
            ->paginated(false)
            ->emptyStateHeading(__('admin.dashboard.empty.no_sales'))
            ->columns([
                TextColumn::make('title')
                    ->label(__('admin.dashboard.widgets.top_products'))
                    // The live product title, not the name denormalised into the
                    // order line.
                    ->limit(40),
                TextColumn::make('sold')
                    ->label(__('admin.dashboard.columns.sold'))
                    ->state(fn (Product $record): int => $aggregates[$record->getKey()]['sold'] ?? 0),
                TextColumn::make('revenue')
                    ->label(__('admin.dashboard.columns.revenue'))
                    ->state(fn (Product $record): float => $aggregates[$record->getKey()]['revenue'] ?? 0.0)
                    ->money(),
            ])
            ->recordActions([
                Action::make('view')
                    ->icon(Heroicon::OutlinedPencilSquare)
                    ->iconButton()
                    ->url(fn (Product $record): string => ProductResource::getUrl(parameters: [
                        'tableAction' => 'edit',
                        'tableActionRecord' => $record->getKey(),
                    ])),
            ], position: RecordActionsPosition::BeforeColumns);
    }
}
