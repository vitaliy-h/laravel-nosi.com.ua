<?php

namespace App\Filament\Resources\Geo\GeoCities;

use App\Filament\Actions\BaseEditAction;
use App\Filament\Resources\Geo\GeoCities\Pages\ManageGeoCities;
use App\Modules\Geo\Models\GeoCity;
use App\Modules\Geo\Models\GeoCountry;
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
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class GeoCityResource extends Resource
{
    protected static ?string $model = GeoCity::class;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(12)
            ->components([
                TextInput::make('title')
                    ->columnSpan(8)
                    ->required(),
                TextInput::make('rank')
                    ->numeric()
                    ->columnSpan(2)
                    ->default(1),
                Toggle::make('display')
                    ->columnSpan(2)
                    ->inline(false)
                    ->default(true),
                Select::make('geo_country_id')
                    ->relationship('country', 'title')
                    ->searchable()
                    ->preload()
                    ->columnSpan(6),
                Select::make('geo_region_id')
                    ->relationship('region', 'title')
                    ->searchable()
                    ->preload()
                    ->columnSpan(6),
                TextInput::make('latitude')
                    ->numeric()
                    ->columnSpan(3),
                TextInput::make('longitude')
                    ->numeric()
                    ->columnSpan(3),
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
                TextColumn::make('region.title')
                    ->label(__('admin.fields.geo_region_id')),
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
                Filter::make('geo_location')
                    ->schema([
                        Select::make('geo_country_id')
                            ->label(__('admin.fields.geo_country_id'))
                            ->options(GeoCountry::orderBy('title')->pluck('title', 'id'))
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(fn (callable $set) => $set('geo_region_id', null)),
                        Select::make('geo_region_id')
                            ->label(__('admin.fields.geo_region_id'))
                            ->options(fn (callable $get) => GeoRegion::query()
                                ->when($get('geo_country_id'), fn (Builder $q, $v) => $q->where('geo_country_id', $v))
                                ->orderBy('title')
                                ->pluck('title', 'id')
                            )
                            ->searchable(),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['geo_country_id'] ?? null, fn (Builder $q, $v) => $q->where('geo_country_id', $v))
                        ->when($data['geo_region_id'] ?? null, fn (Builder $q, $v) => $q->where('geo_region_id', $v))
                    )
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if (!empty($data['geo_country_id'])) {
                            $indicators[] = GeoCountry::find($data['geo_country_id'])?->title;
                        }
                        if (!empty($data['geo_region_id'])) {
                            $indicators[] = GeoRegion::find($data['geo_region_id'])?->title;
                        }

                        return array_filter($indicators);
                    }),
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
            'index' => ManageGeoCities::route('/'),
        ];
    }
}
