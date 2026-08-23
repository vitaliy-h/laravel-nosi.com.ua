<?php

namespace App\Filament\Resources\Shop\Cart;

use App\Filament\Actions\BaseEditAction;
use App\Filament\Filters\UserSelectFilter;
use App\Filament\Forms\UserSelect;
use App\Filament\Resources\Shop\Cart\Pages\ManageCarts;
use App\Modules\Shop\Models\Product;
use App\Modules\ShopCart\Models\Cart;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CartResource extends Resource
{
    protected static ?string $model = Cart::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(12)
            ->components([
                UserSelect::make()
                    ->columnSpan(6),
                TextInput::make('total_amount')
                    ->numeric()
                    ->disabled()
                    ->columnSpan(6),

                Select::make('promocode_id')
                    ->label(__('admin.fields.promocode_id'))
                    ->relationship('promocode', 'code')
                    ->searchable()
                    ->preload()
                    ->columnSpan(4),
                // A cart's discount is always derived from the promocode —
                // CartObserver recalculates it after any change here.
                TextInput::make('discount_amount')
                    ->numeric()
                    ->minValue(0)
                    ->columnSpan(4),
                TextInput::make('subtotal_amount')
                    ->numeric()
                    ->disabled()
                    ->columnSpan(4),

                Repeater::make('items')
                    ->relationship()
                    ->columnSpanFull()
                    ->columns(12)
                    ->schema([
                        Select::make('shop_product_id')
                            ->label(__('admin.tables.shop_products'))
                            ->options(Product::pluck('title', 'id'))
                            ->searchable()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($product = Product::find($state)) {
                                    $set('product_name', $product->title);
                                    $set('product_article', $product->article);
                                    $set('selling_price', $product->price);
                                    $set('total', round($product->price, 2));
                                } else {
                                    $set('product_name', '');
                                    $set('product_article', '');
                                    $set('selling_price', 0);
                                    $set('total', 0);
                                }
                            })
                            ->columnSpan(4),
                        TextInput::make('product_name')
                            ->required()
                            ->columnSpan(4),
                        TextInput::make('product_article')
                            ->columnSpan(2),
                        TextInput::make('selling_price')
                            ->numeric()
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn ($state, callable $get, callable $set) => $set('total', round((float) $state * (float) $get('quantity'), 2)))
                            ->columnSpan(2),
                        TextInput::make('quantity')
                            ->integer()
                            ->default(1)
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn ($state, callable $get, callable $set) => $set('total', round((float) $get('selling_price') * (float) $state, 2)))
                            ->columnSpan(2),
                        TextInput::make('total')
                            ->numeric()
                            ->disabled()
                            ->dehydrated()
                            ->columnSpan(2),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['user', 'items']))
            ->defaultSort('created_at', 'DESC')
            ->columns([
                TextColumn::make('uuid')
                    ->searchable(),
                TextColumn::make('user.select_label')
                    ->label(__('admin.fields.user_id'))
                    ->searchable(['name', 'surname']),
                TextColumn::make('total_amount')
                    ->sortable()
                    ->money(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                UserSelectFilter::make(),
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
            'index' => ManageCarts::route('/'),
        ];
    }
}
