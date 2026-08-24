<?php

namespace App\Filament\Widgets\System;

use App\Filament\Resources\Logs\LogResource;
use App\Modules\Core\Enums\LogEventEnum;
use App\Modules\Core\Enums\LogSourceEnum;
use App\Modules\Core\Models\Log;
use App\Filament\Dashboard\Queries\System\GetRecentActivityQuery;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class RecentActivityWidget extends TableWidget
{
    protected static ?int $sort = 12;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(app(GetRecentActivityQuery::class)->handle())
            ->heading(__('admin.dashboard.widgets.recent_activity'))
            ->paginated(false)
            ->emptyStateHeading(__('admin.dashboard.empty.no_activity'))
            ->columns([
                TextColumn::make('created_at')
                    ->label(__('admin.fields.created_at'))
                    ->dateTime(),
                TextColumn::make('user.name')
                    ->label(__('admin.fields.user'))
                    // System-initiated changes have no user attached.
                    ->default(__('admin.dashboard.values.system')),
                TextColumn::make('event')
                    ->label(__('admin.fields.event'))
                    ->badge()
                    ->color(fn (?LogEventEnum $state): string => match ($state) {
                        LogEventEnum::CREATED => 'success',
                        LogEventEnum::DELETED => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('model_type')
                    ->label(__('admin.fields.model_type'))
                    ->formatStateUsing(fn (?string $state): ?string => $state === null ? null : class_basename($state)),
                TextColumn::make('source')
                    ->label(__('admin.fields.source'))
                    ->badge()
                    ->formatStateUsing(fn (?LogSourceEnum $state): ?string => $state?->value),
            ])
            ->recordActions([
                Action::make('view')
                    ->icon(Heroicon::OutlinedEye)
                    ->iconButton()
                    ->url(fn (Log $record): string => LogResource::getUrl(parameters: [
                        'tableAction' => 'view',
                        'tableActionRecord' => $record->getKey(),
                    ])),
            ], position: RecordActionsPosition::BeforeColumns);
    }
}
