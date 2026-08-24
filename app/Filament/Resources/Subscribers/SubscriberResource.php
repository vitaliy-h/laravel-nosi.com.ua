<?php

namespace App\Filament\Resources\Subscribers;

use App\Filament\Actions\BaseEditAction;
use App\Filament\Resources\Subscribers\Pages\ManageSubscribers;
use App\Modules\Subscribers\Models\Subscriber;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SubscriberResource extends Resource
{
    protected static ?string $model = Subscriber::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelopeOpen;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(12)
            ->components([
                TextInput::make('email')
                    ->columnSpan(5)
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true),
                TextInput::make('ip')
                    ->columnSpan(3),
                DateTimePicker::make('integrated_at')
                    ->columnSpan(4),
                Textarea::make('error')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('email')
                    ->searchable(),
                TextColumn::make('ip')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('integrated_at')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('—'),
                TextColumn::make('error')
                    ->limit(40)
                    ->tooltip(fn (?string $state): ?string => $state)
                    ->toggleable(),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('id')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('integrated_at')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('integrated_at'),
                        false: fn (Builder $query) => $query->whereNull('integrated_at'),
                        blank: fn (Builder $query) => $query,
                    ),
                // Integration failures: linked to from the dashboard "Needs attention" widget.
                TernaryFilter::make('error')
                    ->label(__('admin.fields.error'))
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('error'),
                        false: fn (Builder $query) => $query->whereNull('error'),
                        blank: fn (Builder $query) => $query,
                    ),
            ])
            ->recordActions([
                BaseEditAction::make()->iconButton(),
                DeleteAction::make()->iconButton(),
            ], position: RecordActionsPosition::BeforeColumns)
            ->toolbarActions([
                CreateAction::make(),
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageSubscribers::route('/'),
        ];
    }
}
