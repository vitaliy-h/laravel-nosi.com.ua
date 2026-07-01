<?php

namespace App\Filament\Resources\Shop\Orders;

use App\Filament\Actions\BaseEditAction;
use App\Filament\Resources\Shop\Orders\Pages\ManageOrders;
use App\Modules\Shop\Enums\PaymentStatusEnum;
use App\Modules\Shop\Enums\PaymentTypeEnum;
use App\Modules\Shop\Models\Order;
use App\Modules\Shop\Models\Product;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingCart;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(12)
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable(
                        [
                            'name',
                            'email',
                        ]
                    )
                    ->preload()
                    ->columnSpan(4),
                TextInput::make('order_number')
                    ->disabled()
                    ->columnSpan(4),
                TextInput::make('total_amount')
                    ->numeric()
                    ->disabled()
                    ->columnSpan(4),

                DateTimePicker::make('payment_date')
                    ->columnSpan(4),


                Select::make('payment_status')
                    ->options(PaymentStatusEnum::options())
                    ->columnSpan(4),
                Select::make('payment_type')
                    ->options(PaymentTypeEnum::options())
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
                            ->afterStateUpdated(fn($state, callable $get, callable $set) => $set('total', round((float)$state * (float)$get('quantity'), 2)))
                            ->columnSpan(2),
                        TextInput::make('quantity')
                            ->integer()
                            ->default(1)
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn($state, callable $get, callable $set) => $set('total', round((float)$get('selling_price') * (float)$state, 2)))
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
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('order_number')
                    ->searchable(),
                TextColumn::make('user.name')
                    ->label(__('admin.fields.user_id'))
                    ->searchable(),
                TextColumn::make('payment_status')
                    ->badge()
                    ->formatStateUsing(fn($state) => $state?->label()),
                TextColumn::make('payment_type')
                    ->badge()
                    ->formatStateUsing(fn($state) => $state?->label()),

                TextColumn::make('total_amount')
                    ->sortable()
                    ->money(),
                TextColumn::make('payment_date')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('payment_status')
                    ->options(PaymentStatusEnum::options()),
                SelectFilter::make('payment_type')
                    ->options(PaymentTypeEnum::options()),
                SelectFilter::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
               Action::make('view_public')
                    ->icon(Heroicon::OutlinedGlobeAlt)
                    ->iconButton()
                    ->url(fn (Order $record) => route('order.show', $record->uuid))
                    ->openUrlInNewTab(),
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
            'index' => ManageOrders::route('/'),
        ];
    }
}
