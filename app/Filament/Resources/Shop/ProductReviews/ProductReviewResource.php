<?php

namespace App\Filament\Resources\Shop\ProductReviews;

use App\Filament\Actions\BaseEditAction;
use App\Filament\Filters\UserSelectFilter;
use App\Filament\Forms\UserSelect;
use App\Filament\Resources\Shop\ProductReviews\Pages\ManageProductReviews;
use App\Modules\Shop\Models\ProductReview;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductReviewResource extends Resource
{
    protected static ?string $model = ProductReview::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedStar;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(12)
            ->components([
                Select::make('product_id')
                    ->label(__('admin.fields.product_id'))
                    ->relationship('product', 'title')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->columnSpan(6),
                UserSelect::make()
                    ->columnSpan(4),
                Toggle::make('display')
                    ->inline(false)
                    ->default(false)
                    ->columnSpan(2),
                TextInput::make('name')
                    ->columnSpan(3),
                TextInput::make('city')
                    ->label(__('admin.fields.city'))
                    ->columnSpan(2),
                TextInput::make('email')
                    ->email()
                    ->columnSpan(3),
                Select::make('rating')
                    ->options(static::ratingOptions())
                    ->required()
                    ->columnSpan(2),
                // Lets an admin backdate an imported review.
                DateTimePicker::make('created_at')
                    ->columnSpan(2),
                Textarea::make('text')
                    ->rows(5)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('product.title')
                    ->label(__('admin.fields.product_id'))
                    ->searchable()
                    ->limit(40),
                TextColumn::make('author_name')
                    ->label(__('admin.fields.name'))
                    // Accessor, not a column: search both the typed name and the account name.
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query
                        ->where('name', 'like', "%{$search}%")
                        ->orWhereHas('user', fn (Builder $q) => $q->where('name', 'like', "%{$search}%"))),
                TextColumn::make('city')
                    ->label(__('admin.fields.city'))
                    ->searchable(),
                TextColumn::make('rating')
                    ->badge()
                    ->sortable(),
                ToggleColumn::make('display'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('id')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Moderation queue: unpublished reviews first.
                TernaryFilter::make('display'),
                SelectFilter::make('product_id')
                    ->label(__('admin.fields.product_id'))
                    ->relationship('product', 'title')
                    ->searchable()
                    ->preload(),
                UserSelectFilter::make(),
                SelectFilter::make('rating')
                    ->options(static::ratingOptions()),
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
            'index' => ManageProductReviews::route('/'),
        ];
    }

    /**
     * @return array<int, string>
     */
    private static function ratingOptions(): array
    {
        return [5 => '5', 4 => '4', 3 => '3', 2 => '2', 1 => '1'];
    }
}
