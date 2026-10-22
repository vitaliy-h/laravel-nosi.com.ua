<?php

namespace App\Filament\Resources\Geo\GeoCountries;

use App\Filament\Actions\BaseEditAction;
use App\Filament\Resources\Geo\GeoCountries\Pages\ManageGeoCountries;
use App\Filament\Tables\Columns\InlineEditColumn;
use App\Modules\Geo\Models\GeoCountry;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Table;

class GeoCountryResource extends Resource
{
    protected static ?string $model = GeoCountry::class;

    public static function getModelLabel(): string
    {
        return __('admin.models.geo_country');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.tables.geo_countries');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(12)
            ->components([
                TextInput::make('title')
                    ->columnSpan(6)
                    ->required(),
                TextInput::make('rank')
                    ->numeric()
                    ->columnSpan(2)
                    ->default(1),
                Toggle::make('display')
                    ->columnSpan(3)
                    ->inline(false)
                    ->default(true),
                TextInput::make('iso2')
                    ->columnSpan(3),
                TextInput::make('iso3')
                    ->columnSpan(3),
                TextInput::make('phone_code')
                    ->columnSpan(3),
                TextInput::make('currency_code')
                    ->columnSpan(3),
                TextInput::make('timezone')
                    ->columnSpan(4),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('rank', 'desc')
            ->columns([
                InlineEditColumn::make('title')
                    ->rules(['required', 'max:255'])
                    ->searchable(),
                TextColumn::make('iso2')
                    ->searchable(),
                TextColumn::make('iso3')
                    ->searchable(),
                TextColumn::make('phone_code'),
                TextColumn::make('currency_code'),
                TextColumn::make('timezone'),
                InlineEditColumn::make('rank')
                    ->editAsNumber()
                    ->rules(['required', 'integer'])
                    ->sortable()
                    ->alignEnd(),
                ToggleColumn::make('display'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
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
            'index' => ManageGeoCountries::route('/'),
        ];
    }
}
