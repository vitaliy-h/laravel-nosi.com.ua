<?php

namespace App\Filament\Resources\Geo\GeoRegions;

use App\Filament\Actions\BaseEditAction;
use App\Filament\Resources\Geo\GeoRegions\Pages\ManageGeoRegions;
use App\Modules\Geo\Models\GeoRegion;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class GeoRegionResource extends Resource
{
    protected static ?string $model = GeoRegion::class;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(12)
            ->components([
                TextInput::make('title')
                    ->columnSpan(7)
                    ->required(),
                TextInput::make('rank')
                    ->numeric()
                    ->columnSpan(2)
                    ->default(1),
                Toggle::make('display')
                    ->columnSpan(3)
                    ->inline(false)
                    ->default(true),
                Select::make('geo_country_id')
                    ->relationship('country', 'title')
                    ->searchable()
                    ->preload()
                    ->columnSpan(4),
                TextInput::make('code')
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
                TextColumn::make('country.title')
                    ->label(__('admin.fields.geo_country_id')),
                TextColumn::make('code')
                    ->searchable(),
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
            ->filters([
                SelectFilter::make('geo_country_id')
                    ->relationship('country', 'title')
                    ->searchable()
                    ->preload(),
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
            'index' => ManageGeoRegions::route('/'),
        ];
    }
}
