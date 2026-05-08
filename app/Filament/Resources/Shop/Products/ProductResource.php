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
                    ->label(__('admin.fields.title'))
                    ->columnSpan(5)
                    ->required(),
                TextInput::make('slug')
                    ->label(__('admin.fields.slug'))
                    ->columnSpan(3),
                TextInput::make('rank')
                    ->label(__('admin.fields.rank'))
                    ->numeric()
                    ->columnSpan(2)
                    ->default(0),
                Toggle::make('display')
                    ->label(__('admin.fields.display'))
                    ->inline(false)
                    ->default(true),

                TextInput::make('article')
                    ->label(__('admin.fields.article'))
                    ->default(null)
                    ->columnSpan(4),
                Select::make('category_id')
                    ->label(__('admin.fields.category'))
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
                    ->label(__('admin.fields.brand'))
                    ->options(Brand::pluck('title', 'id'))
                    ->searchable()
                    ->preload()
                    ->columnSpan(4),

                TextInput::make('price')
                    ->label(__('admin.fields.price'))
                    ->numeric()
                    ->columnSpan(2),
                TextInput::make('price2')
                    ->label(__('admin.fields.price2'))
                    ->numeric()
                    ->columnSpan(2),

/*
                FileUpload::make('img')
                    ->label(__('admin.fields.img'))
                    ->image()
                    ->columnSpan(12)
                    ->visibility('public')
                    ->getUploadedFileNameForStorageUsing(function ($file, $record) {
                        return ($record ?? new Product())->getFilePath(
                            $file->getClientOriginalName(),
                            'img',
                        );
                    }),
*/
                RichEditor::make('text')
                    ->label(__('admin.fields.text'))
                    ->columnSpanFull(),
                TextInput::make('meta_title')
                    ->label(__('admin.fields.meta_title'))
                    ->columnSpanFull(),
                Textarea::make('meta_description')
                    ->label(__('admin.fields.meta_description'))
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('img')
                    ->label(__('admin.fields.img')),
                TextColumn::make('title')
                    ->label(__('admin.fields.title'))
                    ->searchable(),
                TextColumn::make('slug')
                    ->label(__('admin.fields.slug'))
                    ->searchable(),
                TextColumn::make('article')
                    ->label(__('admin.fields.article'))
                    ->searchable(),

                TextColumn::make('category.title')
                    ->label(__('admin.fields.category'))
                    ->sortable(),

                TextColumn::make('brand.title')
                    ->label(__('admin.fields.brand'))
                    ->sortable(),

                TextColumn::make('price')
                    ->label(__('admin.fields.price'))
                    ->money()
                    ->default(0)
                    ->sortable(),
                TextColumn::make('price2')
                    ->label(__('admin.fields.price2'))
                    ->money()
                    ->default(0)
                    ->sortable(),

                ToggleColumn::make('display')
                    ->label(__('admin.fields.display')),
                TextColumn::make('created_at')
                    ->label(__('admin.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('admin.fields.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
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
