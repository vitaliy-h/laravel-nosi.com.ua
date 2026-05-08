<?php

namespace App\Filament\Resources\Shop\Products;

use App\Filament\Actions\BaseEditAction;
use App\Filament\Resources\Shop\Products\Pages\ManageProducts;
use App\Models\Shop\Product;
use App\Modules\News\Models\News;
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
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

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
                TextInput::make('category_id')
                    ->label(__('admin.fields.category'))
                    ->numeric()
                    ->columnSpan(3),
                TextInput::make('brand_id')
                    ->label(__('admin.fields.brand'))
                    ->numeric()
                    ->columnSpan(3),
                TextInput::make('price')
                    ->label(__('admin.fields.price'))
                    ->numeric()
                    ->columnSpan(3),
                TextInput::make('price2')
                    ->label(__('admin.fields.price2'))
                    ->numeric()
                    ->columnSpan(3),
                TextInput::make('article')
                    ->label(__('admin.fields.article'))
                    ->default(null)
                    ->columnSpan(3),
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

                TextColumn::make('category_id')
                    ->label(__('admin.fields.category'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('brand_id')
                    ->label(__('admin.fields.brand'))
                    ->numeric()
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
