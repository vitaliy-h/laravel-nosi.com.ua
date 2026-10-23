<?php

namespace App\Filament\Resources\Shop\Categories;

use App\Filament\Actions\BaseEditAction;
use App\Filament\Resources\Shop\Categories\Pages\ManageCategories;
use App\Filament\Support\NestedSetFields;
use App\Filament\Tables\Columns\InlineEditColumn;
use App\Filament\Traits\HasSeoFields;
use App\Modules\Shop\Models\Category;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Table;

class CategoryResource extends Resource
{
    use HasSeoFields;

    protected static ?string $model = Category::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function getModelLabel(): string
    {
        return __('admin.models.shop_category');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.tables.shop_categories');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(12)
            ->components([
                TextInput::make('title')
                    ->columnSpan(6)
                    ->required(),
                /*
                TextInput::make('rank')
                    ->required()
                    ->numeric()
                    ->columnSpan(3)
                    ->default(0),
                */
                Toggle::make('display')
                    ->default(true)
                    ->inline(false)
                    ->columnSpan(2),
                Toggle::make('display_menu')
                    ->inline(false)
                    ->columnSpan(2),
                Toggle::make('display_menu_footer')
                    ->inline(false)
                    ->columnSpan(2),
                ...NestedSetFields::make(Category::class),

                FileUpload::make('img')
                    ->image()
                    ->columnSpan(6)
                    ->visibility('public')
                    ->getUploadedFileNameForStorageUsing(function ($file, $record) {
                        return ($record ?? new Category)->getFilePath(
                            $file->getClientOriginalName(),
                            'img',
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
            ->modifyQueryUsing(fn ($query) => $query->withDepth()->defaultOrder()
            )
            ->defaultSort('_lft')
            ->columns([
                ImageColumn::make('img'),
                InlineEditColumn::make('title')
                    ->rules(['required', 'max:255'])
                    ->formatStateUsing(fn ($record) => str_repeat('— ', $record->depth) . $record->title
                    )
                    ->searchable(),
                static::getSlugColumn(),
                ToggleColumn::make('display_menu'),
                ToggleColumn::make('display_menu_footer'),
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
                //
            ])
            ->recordActions([
                ViewAction::make()
                    ->iconButton()
                    ->url(fn (Category $record) => save_route('shop.category', $record->slug))
                    ->openUrlInNewTab(),
                BaseEditAction::make()->iconButton(),
                DeleteAction::make()
                    ->iconButton()
                    ->disabled(fn ($record) => $record->children()->exists() ||
                        $record->products()->exists()
                    )
                    ->tooltip(fn ($record) => match (true) {
                        $record->children()->exists() => __('admin.messages.delete_forbidden_has_children'),
                        $record->products()->exists() => __('admin.messages.delete_forbidden_has_products'),
                        default                       => null,
                    }),
            ], position: RecordActionsPosition::BeforeColumns)
            ->toolbarActions([
                NestedSetFields::createAction(),
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCategories::route('/'),
        ];
    }
}
