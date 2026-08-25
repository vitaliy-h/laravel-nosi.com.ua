<?php

namespace App\Filament\Widgets\Shop;

use App\Filament\Resources\Shop\Orders\OrderResource;
use App\Modules\Core\Support\ModuleGate;
use App\Modules\ShopOrders\Enums\PaymentStatusEnum;
use App\Modules\ShopOrders\Models\Order;
use App\Filament\Dashboard\Queries\Shop\GetLatestOrdersQuery;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

/**
 * The most recent orders regardless of the period filter — this is the "what
 * just came in" list, not a per-period report, so it does not use
 * InteractsWithDashboardPeriod.
 */
class LatestOrdersWidget extends TableWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return ModuleGate::isEnabled('shop_cart');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(app(GetLatestOrdersQuery::class)->handle())
            // TableWidget always sets some heading before ->table() runs (an
            // auto-generated one from the class name, if none given here) —
            // pass null explicitly to actually suppress it.
            ->heading(null)
            ->paginated(false)
            ->emptyStateHeading(__('admin.dashboard.empty.no_orders'))
            ->columns([
                TextColumn::make('order_number')
                    ->label(__('admin.dashboard.widgets.latest_orders')),
                TextColumn::make('customer')
                    ->label(__('admin.dashboard.columns.customer'))
                    // Guest orders have no user row: fall back to the name typed
                    // at checkout.
                    ->state(fn (Order $record): string => $record->user?->select_label
                        ?: trim(($record->user_surname ?? '').' '.($record->user_name ?? ''))
                        ?: ($record->user_email ?? __('admin.dashboard.values.none'))),
                TextColumn::make('total_amount')
                    ->label(__('admin.fields.total_amount'))
                    ->money(),
                TextColumn::make('payment_status')
                    ->label(__('admin.fields.payment_status'))
                    ->badge()
                    ->color(fn (?PaymentStatusEnum $state): string => match ($state) {
                        PaymentStatusEnum::PAID => 'success',
                        PaymentStatusEnum::PROCESSING => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (?PaymentStatusEnum $state): ?string => $state?->label()),
                TextColumn::make('created_at')
                    ->label(__('admin.fields.created_at'))
                    ->dateTime()
                    ->tooltip(fn (Order $record): ?string => $record->created_at?->diffForHumans()),
            ])
            ->recordActions([
                Action::make('view')
                    ->icon(Heroicon::OutlinedPencilSquare)
                    ->iconButton()
                    // Resources here are single-page ManageRecords: deep-link the
                    // edit modal instead of a dedicated edit page.
                    ->url(fn (Order $record): string => OrderResource::getUrl(parameters: [
                        'tableAction' => 'edit',
                        'tableActionRecord' => $record->getKey(),
                    ])),
            ], position: RecordActionsPosition::BeforeColumns);
    }
}
