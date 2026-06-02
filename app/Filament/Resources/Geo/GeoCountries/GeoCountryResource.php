<?php

namespace App\Filament\Resources\Geo\GeoCountries;

use App\Filament\Actions\BaseEditAction;
use App\Filament\Resources\Geo\GeoCountries\Pages\ManageGeoCountries;
use App\Modules\Geo\Models\GeoCountry;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class GeoCountryResource extends Resource
{
    protected static ?string $model = GeoCountry::class;

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
                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('iso2')
                    ->searchable(),
                TextColumn::make('iso3')
                    ->searchable(),
                TextColumn::make('phone_code'),
                TextColumn::make('currency_code'),
                TextColumn::make('timezone'),
                TextInputColumn::make('rank')
                    ->type('number')
                    ->sortable()
                    ->alignEnd()
                    ->width('100px'),
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
            ], position: \Filament\Tables\Enums\RecordActionsPosition::BeforeColumns)
            ->toolbarActions([
                \Filament\Actions\CreateAction::make(),
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
