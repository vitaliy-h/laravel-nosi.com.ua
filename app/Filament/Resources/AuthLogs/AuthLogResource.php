<?php

namespace App\Filament\Resources\AuthLogs;

use App\Filament\Resources\AuthLogs\Pages\ManageAuthLogs;
use App\Modules\Core\Enums\AuthLogActionEnum;
use App\Modules\Core\Enums\AuthLogResultEnum;
use App\Modules\Core\Enums\AuthLogSourceEnum;
use App\Modules\Core\Models\AuthLog;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
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

class AuthLogResource extends Resource
{
    protected static ?string $model = AuthLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFingerPrint;

    public static function getModelLabel(): string
    {
        return __('admin.tables.log_auth');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.tables.log_auth');
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
                    ->label(__('admin.fields.user'))
                    ->searchable(),

                TextColumn::make('login')
                    ->label(__('admin.fields.login'))
                    ->searchable(),

                TextColumn::make('source')
                    ->label(__('admin.fields.source'))
                    ->badge()
                    ->formatStateUsing(fn (AuthLogSourceEnum $state) => $state->label()),

                TextColumn::make('action')
                    ->label(__('admin.fields.action'))
                    ->badge()
                    ->formatStateUsing(fn (AuthLogActionEnum $state) => $state->label()),

                TextColumn::make('result')
                    ->label(__('admin.fields.result'))
                    ->badge()
                    ->color(fn (AuthLogResultEnum $state) => $state->color())
                    ->formatStateUsing(fn (AuthLogResultEnum $state) => $state->label()),

                TextColumn::make('ip_address')
                    ->label(__('admin.fields.ip_address'))
                    ->searchable(),

                TextColumn::make('browser')
                    ->label(__('admin.fields.browser'))
                    ->searchable(),

                TextColumn::make('platform')
                    ->label(__('admin.fields.platform'))
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([

                SelectFilter::make('user_id')
                    ->label(__('admin.fields.user'))
                    ->relationship('user', 'email')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('source')
                    ->label(__('admin.fields.source'))
                    ->options(AuthLogSourceEnum::options()),

                SelectFilter::make('action')
                    ->label(__('admin.fields.action'))
                    ->options(AuthLogActionEnum::options()),

                SelectFilter::make('result')
                    ->label(__('admin.fields.result'))
                    ->options(AuthLogResultEnum::options()),

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
                    })
                    ->indicateUsing(function (array $data): ?string {
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
                    ->modalWidth('3xl')
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
            'index' => ManageAuthLogs::route('/'),
        ];
    }

    private static function getViewSchema(): array
    {
        return [
            Section::make()
                ->schema([

                    Grid::make(2)
                        ->schema([

                            TextEntry::make('created_at')
                                ->dateTime(),

                            TextEntry::make('user.email')
                                ->label(__('admin.fields.user'))
                                ->placeholder('—'),

                            TextEntry::make('login')
                                ->label(__('admin.fields.login')),

                            TextEntry::make('source')
                                ->label(__('admin.fields.source'))
                                ->badge()
                                ->formatStateUsing(fn (AuthLogSourceEnum $state) => $state->label()),

                            TextEntry::make('action')
                                ->label(__('admin.fields.action'))
                                ->badge()
                                ->formatStateUsing(fn (AuthLogActionEnum $state) => $state->label()),

                            TextEntry::make('result')
                                ->label(__('admin.fields.result'))
                                ->badge()
                                ->color(fn (AuthLogResultEnum $state) => $state->color())
                                ->formatStateUsing(fn (AuthLogResultEnum $state) => $state->label()),

                            TextEntry::make('ip_address')
                                ->label(__('admin.fields.ip_address'))
                                ->placeholder('—'),

                            TextEntry::make('browser')
                                ->label(__('admin.fields.browser'))
                                ->placeholder('—'),

                            TextEntry::make('platform')
                                ->label(__('admin.fields.platform'))
                                ->placeholder('—'),
                        ]),

                    TextEntry::make('user_agent')
                        ->label(__('admin.fields.user_agent'))
                        ->placeholder('—')
                        ->columnSpanFull(),
                ]),
        ];
    }
}
