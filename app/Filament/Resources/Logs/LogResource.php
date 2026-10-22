<?php

namespace App\Filament\Resources\Logs;

use App\Filament\Resources\Logs\Pages\ManageLogs;
use App\Modules\Core\Enums\LogEventEnum;
use App\Modules\Core\Enums\LogSourceEnum;
use App\Modules\Core\Models\Log;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LogResource extends Resource
{
    protected static ?string $model = Log::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function getModelLabel(): string
    {
        return __('admin.models.log');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.tables.logs');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'DESC')
            ->columns([
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('user.email')
                    ->searchable(),
                TextColumn::make('ip_address')
                    ->searchable(),
                TextColumn::make('event')
                    ->badge()
                    ->searchable(),
                TextColumn::make('model_type')
                    ->formatStateUsing(fn ($state) => class_basename($state))
                    ->searchable(),
                TextColumn::make('model_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('source')
                    ->badge()
                    ->searchable(),
                TextColumn::make('changed_fields')
                    ->badge(),
                // ->listWithLineBreaks(),
            ])
            ->filters([
                //
                SelectFilter::make(__('admin.fields.user'))
                    ->relationship('user', 'email')
                    ->searchable()
                    ->preload(),

                SelectFilter::make(__('admin.fields.event'))
                    ->options(LogEventEnum::options()),

                SelectFilter::make(__('admin.fields.source'))
                    ->options(LogSourceEnum::options()),

                SelectFilter::make('model_type')
                    ->label(__('admin.fields.model_type'))
                    ->options(function () {
                        return Log::query()
                            ->select('model_type')
                            ->distinct()
                            ->pluck('model_type', 'model_type')
                            ->mapWithKeys(fn ($item) => [
                                $item => class_basename($item),
                            ])
                            ->toArray();
                    }),

                Filter::make('model_id')
                    ->label(__('admin.fields.model_id'))
                    ->schema([
                        TextInput::make('model_id'),
                    ])
                    ->query(function (Builder $query, array $data) {

                        return $query
                            ->when(
                                $data['model_id'] ?? null,
                                fn ($q, $id) => $q->where('model_id', $id)
                            );
                    })
                    ->indicateUsing(function (array $data): ?string {
                        if (empty($data['model_id'])) {
                            return null;
                        }

                        return " #{$data['model_id']}";
                    }),

                Filter::make('created_at')
                    ->schema([
                        DatePicker::make('from')->label(__('admin.fields.from')),
                        DatePicker::make('until')->label(__('admin.fields.until')),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when(
                                $data['from'] ?? null,
                                fn (Builder $query, $date) => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['until'] ?? null,
                                fn (Builder $query, $date) => $query->whereDate('created_at', '<=', $date),
                            );
                    })->indicateUsing(function (array $data): ?string {
                        if (!$data['from'] && !$data['until']) {
                            return null;
                        }

                        return 'Date: ' .
                            ($data['from'] ?? '...') .
                            ' - ' .
                            ($data['until'] ?? '...');
                    }),
            ])
            ->recordActions([

                ViewAction::make()
                    ->iconButton()
                    ->modalWidth('5xl')
                    ->slideOver()
                    ->schema(self::getViewSchema()),

                DeleteAction::make()
                    ->iconButton(),

            ], position: RecordActionsPosition::BeforeColumns)
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageLogs::route('/'),
        ];
    }

    private static function getViewSchema(): array
    {
        return [
            Section::make()
                ->schema([

                    Grid::make(2)
                        ->schema([

                            TextEntry::make('user.email')
                                ->label(__('admin.fields.user')),

                            TextEntry::make('event')
                                ->badge()
                                ->label(__('admin.fields.event')),

                            TextEntry::make('model_type')
                                ->label(__('admin.fields.model_type'))
                                ->formatStateUsing(fn ($state) => class_basename($state)),

                            TextEntry::make('model_id')
                                ->label(__('admin.fields.model_id')),

                            TextEntry::make('source')
                                ->badge()
                                ->label(__('admin.fields.source')),

                            TextEntry::make('ip_address')
                                ->label(__('admin.fields.ip_address')),

                            TextEntry::make('created_at')
                                ->dateTime(),
                        ]),
                ]),

            Section::make(__('admin.fields.changed_fields'))
                ->schema([

                    TextEntry::make('changed_fields')
                        ->label(__('admin.fields.changed_fields'))
                        ->badge()
                        ->separator(',')
                        ->columnSpanFull(),

                    KeyValueEntry::make('old_values')
                        ->label(__('admin.fields.old_values'))
                        ->columnSpanFull(),

                    KeyValueEntry::make('new_values')
                        ->label(__('admin.fields.new_values'))
                        ->columnSpanFull(),
                ]),
        ];
    }
}
