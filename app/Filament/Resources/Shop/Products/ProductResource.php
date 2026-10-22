<?php

namespace App\Filament\Resources\Shop\Products;

use App\Filament\Actions\BaseEditAction;
use App\Filament\Dashboard\Queries\Shop\GetCatalogStatsQuery;
use App\Filament\Resources\Shop\ProductReviews\ProductReviewResource;
use App\Filament\Resources\Shop\Products\Pages\ManageProducts;
use App\Filament\Tables\Columns\InlineEditColumn;
use App\Filament\Traits\HasSeoFields;
use App\Modules\Core\Support\ModuleGate;
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
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
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

                TextInput::make('price')
                    ->numeric()
                    ->columnSpan(2),
                TextInput::make('price2')
                    ->numeric()
                    ->columnSpan(2),

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
                InlineEditColumn::make('price2')
                    ->editAsNumber()
                    ->rules(['numeric', 'min:0'])
                    ->numeric(decimalPlaces: 2)
                    ->alignEnd()
                    ->sortable(),
                // Links through to the reviews of this product, filter already
                // applied — so the column goes away with the shop_product_reviews
                // module, whose resource has no route left to link to.
                TextColumn::make('rating')
                    ->sortable()
                    ->alignEnd()
                    ->color('primary')
                    ->visible(ModuleGate::isEnabled('shop_product_reviews'))
                    ->formatStateUsing(fn (Product $record): string => number_format($record->rating, 2) . ' (' . $record->reviews_count . ')')
                    ->url(fn (Product $record): string => ProductReviewResource::getUrl(parameters: [
                        'filters' => ['product_id' => ['value' => $record->id]],
                    ])),
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
