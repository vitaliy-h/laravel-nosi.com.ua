<?php

namespace App\Filament\Resources\Shop\Attributes;

use App\Filament\Actions\BaseEditAction;
use App\Filament\Resources\Shop\Attributes\Pages\ManageAttributes;
use App\Filament\Tables\Columns\InlineEditColumn;
use App\Modules\Shop\Dtos\ShopFilterCriteriaData;
use App\Modules\Shop\Models\Attribute;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Table;

/**
 * Product parameters (size, season, material…) and their values. A product
 * picks any number of values of each parameter in its own form.
 */
class AttributeResource extends Resource
{
    protected static ?string $model = Attribute::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAdjustmentsHorizontal;

    public static function getModelLabel(): string
    {
        return __('admin.models.shop_attribute');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.tables.shop_attributes');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(12)
            ->components([
                TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->columnSpan(5),
                // The parameter's key in the catalogue filter URL: `?season[]=3`.
                TextInput::make('code')
                    ->required()
                    ->maxLength(50)
                    ->alphaDash()
                    ->notIn(ShopFilterCriteriaData::RESERVED)
                    ->unique(ignoreRecord: true)
                    ->helperText(__('admin.fields.attribute_code_helper'))
                    ->columnSpan(3),
                TextInput::make('rank')
                    ->numeric()
                    ->default(0)
                    ->columnSpan(2),
                Toggle::make('display')
                    ->inline(false)
                    ->default(true)
                    ->columnSpan(1),
                Toggle::make('is_filterable')
                    ->inline(false)
                    ->default(true)
                    ->columnSpan(1),

                Repeater::make('options')
                    ->relationship()
                    ->orderColumn('rank')
                    ->simple(
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                    )
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('rank', 'DESC')
            ->columns([
                InlineEditColumn::make('title')
                    ->rules(['required', 'max:255'])
                    ->searchable(),
                TextColumn::make('code')
                    ->searchable(),
                TextColumn::make('options.title')
                    ->label(__('admin.fields.options'))
                    ->badge()
                    ->limitList(8),
                InlineEditColumn::make('rank')
                    ->editAsNumber()
                    ->rules(['required', 'integer'])
                    ->sortable()
                    ->alignEnd(),
                ToggleColumn::make('is_filterable'),
                ToggleColumn::make('display'),
                TextColumn::make('id')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            'index' => ManageAttributes::route('/'),
        ];
    }
}
