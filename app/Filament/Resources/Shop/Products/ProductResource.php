<?php

namespace App\Filament\Resources\Shop\Products;

use App\Filament\Actions\BaseEditAction;
use App\Filament\Resources\Shop\Products\Pages\ManageProducts;
use App\Models\Shop\Product;
use App\Models\Shop\Category;
use App\Models\Shop\Brand;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\Select;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(12)
            ->components([
                TextInput::make('title')
                    ->columnSpan(5)
                    ->required(),
                TextInput::make('slug')
                    ->columnSpan(3),
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
                                $category->id => str_repeat('— ', $category->depth) . $category->title
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
                    ->columnSpan(4),

                FileUpload::make('img')
                    ->image()
                    ->columnSpan(6)
                    ->visibility('public')
                    ->getUploadedFileNameForStorageUsing(function ($file, $record) {
                        return ($record ?? new Product())->getFilePath(
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

                RichEditor::make('text')
                    ->columnSpanFull(),
                TextInput::make('meta_title')
                    ->columnSpanFull(),
                Textarea::make('meta_description')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('img'),
                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('slug')
                    ->searchable(),
                TextColumn::make('article')
                    ->searchable(),
                TextColumn::make('category.title')
                    ->label(__('admin.fields.category_id')),
                TextColumn::make('brand.title')
                    ->label(__('admin.fields.brand_id')),
                TextColumn::make('price')
                    ->money()
                    ->sortable(),
                TextColumn::make('price2')
                    ->money()
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
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->options(
                        Category::defaultOrder()
                            ->withDepth()
                            ->get()
                            ->toFlatTree()
                            ->mapWithKeys(fn ($category) => [
                                $category->id => str_repeat('— ', $category->depth) . $category->title
                            ])
                    )
                    ->searchable()
                    ->preload(),

                SelectFilter::make('brand_id')
                    ->relationship('brand', 'title')
                    ->searchable()
                    ->preload(),
            ])
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
            'index' => ManageProducts::route('/'),
        ];
    }
}
