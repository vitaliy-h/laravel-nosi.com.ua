<?php

namespace App\Filament\Resources\Shop\Products;

use App\Filament\Actions\BaseEditAction;
use App\Filament\Dashboard\Queries\Shop\GetCatalogStatsQuery;
use App\Filament\Resources\Shop\Products\Pages\ManageProducts;
use App\Filament\Tables\Columns\InlineEditColumn;
use App\Filament\Traits\HasSeoFields;
use App\Modules\Core\Support\ModuleGate;
use App\Modules\Shop\Models\Attribute;
use App\Modules\Shop\Models\Brand;
use App\Modules\Shop\Models\Category;
use App\Modules\Shop\Models\Product;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;

class ProductResource extends Resource
{
    use HasSeoFields;

    protected static ?string $model = Product::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function getModelLabel(): string
    {
        return __('admin.models.shop_product');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.tables.shop_products');
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
                    ->default(0),
                Toggle::make('is_hit')
                    ->label(__('admin.fields.is_hit'))
                    ->inline(false)
                    ->default(false)
                    ->columnSpan(2),
                Toggle::make('display')
                    ->inline(false)
                    ->default(true),

                TextInput::make('article')
                    ->default(null)
                    ->columnSpan(4),
                Select::make('category_id')
                    ->options(fn (): array => static::getCategoryOptions())
                    ->searchable()
                    ->preload()
                    ->required()
                    ->columnSpan(4),
                Select::make('brand_id')
                    ->options(Brand::pluck('title', 'id'))
                    ->searchable()
                    ->preload()
                    ->visible(ModuleGate::isEnabled('shop_brands'))
                    ->columnSpan(4),

                FileUpload::make('img')
                    ->image()
                    ->columnSpan(6)
                    ->visibility('public')
                    ->getUploadedFileNameForStorageUsing(function ($file, $record) {
                        return ($record ?? new Product)->getFilePath(
                            $file->getClientOriginalName(),
                            'img',
                        );
                    }),

                Toggle::make('is_pack')
                    ->inline(false)
                    ->default(false)
                    ->helperText(__('admin.fields.is_pack_helper'))
                    ->columnSpan(2),

                // The sets the product is sold in; Product keeps `price` at the
                // price of the smallest one for the catalogue filter and sorting.
                Repeater::make('packs')
                    ->schema([
                        TextInput::make('quantity')
                            ->label(__('admin.fields.pack_quantity'))
                            ->integer()
                            ->minValue(1)
                            ->required()
                            ->distinct(),
                        TextInput::make('price')
                            ->numeric()
                            ->minValue(0),
                    ])
                    ->columns(2)
                    ->default([['quantity' => 1, 'price' => null], ['quantity' => 5, 'price' => null], ['quantity' => 10, 'price' => null]])
                    ->minItems(1)
                    ->maxItems(Product::MAX_PACKS)
                    ->reorderable(false)
                    ->helperText(__('admin.fields.packs_helper'))
                    ->columnSpan(4),

                FileUpload::make('imgs')
                    ->image()
                    ->multiple()
                    ->reorderable()
                    ->columnSpan(12)
                    ->panelLayout('grid')
                    ->imagePreviewHeight('150')
                    ->visibility('public')
                    ->getUploadedFileNameForStorageUsing(function ($file, $record) {
                        return ($record ?? new Product)->getFilePath(
                            $file->getClientOriginalName(),
                            'gallery',
                        );
                    }),

                Section::make(__('admin.tables.shop_attributes'))
                    ->schema(fn (): array => static::getAttributeFields())
                    ->columns(3)
                    ->visible(fn (): bool => Attribute::query()->exists())
                    ->columnSpanFull(),

                RichEditor::make('text')
                    ->columnSpanFull(),
                static::getSeoSection(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('rank', 'DESC')
            ->columns([
                ImageColumn::make('img'),
                InlineEditColumn::make('title')
                    ->rules(['required', 'max:255'])
                    ->searchable(),
                TextColumn::make('article')
                    ->searchable(),
                // Changing the category moves the product's public URL — the
                // category slug is part of `shop.product`.
                InlineEditColumn::make('category.title')
                    ->label(__('admin.fields.category_id'))
                    ->editAttribute('category_id')
                    ->options(fn (): array => static::getCategoryOptions())
                    ->rules(['required', Rule::exists(Category::class, 'id')]),
                InlineEditColumn::make('brand.title')
                    ->label(__('admin.fields.brand_id'))
                    ->editAttribute('brand_id')
                    ->options(fn (): array => Brand::pluck('title', 'id')->all())
                    ->rules([Rule::exists(Brand::class, 'id')])
                    ->visible(ModuleGate::isEnabled('shop_brands')),
                InlineEditColumn::make('price')
                    ->editAsNumber()
                    ->rules(['numeric', 'min:0'])
                    ->numeric(decimalPlaces: 2)
                    ->alignEnd()
                    ->sortable(),
                InlineEditColumn::make('rank')
                    ->editAsNumber()
                    ->rules(['required', 'integer'])
                    ->sortable()
                    ->alignEnd(),
                ToggleColumn::make('is_hit')
                    ->label(__('admin.fields.is_hit'))
                    ->sortable(),
                ToggleColumn::make('display'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('id')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->options(fn (): array => static::getCategoryOptions())
                    ->searchable()
                    ->preload(),

                SelectFilter::make('brand_id')
                    ->relationship('brand', 'title')
                    ->searchable()
                    ->preload()
                    ->visible(ModuleGate::isEnabled('shop_brands')),

                TernaryFilter::make('is_hit')
                    ->label(__('admin.fields.is_hit')),

                // Products that cannot be sold as they are: linked to from the
                // dashboard Catalog widget.
                Filter::make('incomplete')
                    ->label(__('admin.dashboard.stats.incomplete_products'))
                    ->query(fn (Builder $query): Builder => GetCatalogStatsQuery::incomplete($query)),
            ])
            ->recordActions([
                ViewAction::make()
                    ->iconButton()
                    ->url(fn (Product $record) => save_route('shop.product', [
                        $record->category->slug,
                        $record->slug_with_id,
                    ]))
                    ->openUrlInNewTab(),
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

    /**
     * A multiple choice per parameter (size, season, material…). All of them
     * share one pivot, so each field reads and syncs only its own parameter's
     * values and leaves the others' alone.
     *
     * @return array<int, Select>
     */
    protected static function getAttributeFields(): array
    {
        return Attribute::query()
            ->with('options')
            ->orderByDesc('rank')
            ->orderBy('id')
            ->get()
            ->map(fn (Attribute $attribute): Select => Select::make('attribute_options_' . $attribute->id)
                ->label($attribute->title)
                ->multiple()
                ->options($attribute->options->pluck('title', 'id')->all())
                ->afterStateHydrated(fn (Select $component, ?Product $record) => $component->state(
                    $record?->attributeOptions()
                        ->where('attribute_id', $attribute->id)
                        ->pluck('shop_attribute_options.id')
                        ->all() ?? []
                ))
                ->dehydrated(false)
                ->saveRelationshipsUsing(function (Product $record, ?array $state) use ($attribute): void {
                    $others = $record->attributeOptions()
                        ->where('attribute_id', '!=', $attribute->id)
                        ->pluck('shop_attribute_options.id')
                        ->all();

                    $own = $attribute->options()
                        ->whereIn('id', array_map('intval', $state ?? []))
                        ->pluck('id')
                        ->all();

                    $record->attributeOptions()->sync([...$others, ...$own]);
                }))
            ->all();
    }

    /**
     * Categories as a flat tree, indented by depth — shared by the form,
     * the filter and the inline category column.
     *
     * @return array<int, string>
     */
    protected static function getCategoryOptions(): array
    {
        return Category::defaultOrder()
            ->withDepth()
            ->get()
            ->toFlatTree()
            ->mapWithKeys(fn ($category) => [
                $category->id => str_repeat('— ', $category->depth) . $category->title,
            ])
            ->all();
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageProducts::route('/'),
        ];
    }
}
