<?php

namespace App\Filament\Resources\Shop\Promocodes;

use App\Filament\Actions\BaseEditAction;
use App\Filament\Resources\Shop\Promocodes\Pages\ManagePromocodes;
use App\Modules\ShopCart\Enums\PromocodeTypeEnum;
use App\Modules\ShopCart\Models\Promocode;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PromocodeResource extends Resource
{
    protected static ?string $model = Promocode::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTicket;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(12)
            ->components([
                TextInput::make('code')
                    ->required()
                    ->maxLength(64)
                    ->unique(ignoreRecord: true)
                    ->columnSpan(4),
                Select::make('type')
                    ->options(PromocodeTypeEnum::options())
                    ->default(PromocodeTypeEnum::FIXED->value)
                    ->required()
                    ->columnSpan(4),
                TextInput::make('value')
                    ->numeric()
                    ->minValue(0)
                    ->required()
                    ->columnSpan(2),
                Toggle::make('display')
                    ->inline(false)
                    ->default(true)
                    ->columnSpan(2),

                TextInput::make('min_order_amount')
                    ->numeric()
                    ->minValue(0)
                    ->columnSpan(3),
                // Only meaningful for percent codes.
                TextInput::make('max_discount')
                    ->numeric()
                    ->minValue(0)
                    ->columnSpan(3),
                TextInput::make('usage_limit')
                    ->integer()
                    ->minValue(0)
                    ->columnSpan(3),
                TextInput::make('used_count')
                    ->integer()
                    ->disabled()
                    ->columnSpan(3),

                DateTimePicker::make('starts_at')
                    ->columnSpan(3),
                DateTimePicker::make('expires_at')
                    ->after('starts_at')
                    ->columnSpan(3),

                Textarea::make('comment')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('code')
                    ->searchable(),
                TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state?->label()),
                TextColumn::make('discount_label')
                    ->label(__('admin.fields.discount_label')),
                TextColumn::make('used_count'),
                TextColumn::make('usage_limit'),
                TextColumn::make('expires_at')
                    ->dateTime()
                    ->sortable(),
                ToggleColumn::make('display'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('id')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('display'),
                SelectFilter::make('type')
                    ->options(PromocodeTypeEnum::options()),
                // Same predicate as Promocode::scopeActive().
                Filter::make('active')
                    ->label(__('admin.fields.promocode_active'))
                    ->query(fn (Builder $query): Builder => $query->active()),
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
            'index' => ManagePromocodes::route('/'),
        ];
    }
}
