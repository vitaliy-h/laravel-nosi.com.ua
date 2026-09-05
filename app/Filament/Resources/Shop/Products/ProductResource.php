<?php

namespace App\Filament\Resources\Shop\Products;

use App\Filament\Actions\BaseEditAction;
use App\Filament\Dashboard\Queries\Shop\GetCatalogStatsQuery;
use App\Filament\Resources\Shop\ProductReviews\ProductReviewResource;
use App\Filament\Resources\Shop\Products\Pages\ManageProducts;
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
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductResource extends Resource
{
    use HasSeoFields;

    protected static ?string $model = Product::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

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
                    ->default(0),
                Toggle::make('display')
                    ->inline(false)
                    ->default(true),

                TextInput::make('article')
                    ->default(null)
                    ->columnSpan(4),
                Select::make('category_id')
                    ->options(
                        Category::defaultOrder()
                            ->withDepth()
                            ->get()
                            ->toFlatTree()
                            ->mapWithKeys(fn ($category) => [
                                $category->id => str_repeat('— ', $category->depth) . $category->title,
                            ])
                    )
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
                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('article')
                    ->searchable(),
                TextColumn::make('category.title')
                    ->label(__('admin.fields.category_id')),
                TextColumn::make('brand.title')
                    ->label(__('admin.fields.brand_id'))
                    ->visible(ModuleGate::isEnabled('shop_brands')),
                TextColumn::make('price')
                    ->money()
                    ->sortable(),
                TextColumn::make('price2')
                    ->money()
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
                TextColumn::make('rank')
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
                TextColumn::make('id')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->options(
                        Category::defaultOrder()
                            ->withDepth()
                            ->get()
                            ->toFlatTree()
                            ->mapWithKeys(fn ($category) => [
                                $category->id => str_repeat('— ', $category->depth) . $category->title,
                            ])
                    )
                    ->searchable()
                    ->preload(),

                SelectFilter::make('brand_id')
                    ->relationship('brand', 'title')
                    ->searchable()
                    ->preload()
                    ->visible(ModuleGate::isEnabled('shop_brands')),

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

    public static function getPages(): array
    {
        return [
            'index' => ManageProducts::route('/'),
        ];
    }
}
