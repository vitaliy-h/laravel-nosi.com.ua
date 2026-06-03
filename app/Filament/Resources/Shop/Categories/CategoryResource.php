<?php

namespace App\Filament\Resources\Shop\Categories;

use App\Filament\Actions\BaseEditAction;
use App\Filament\Resources\Shop\Categories\Pages\ManageCategories;
use App\Filament\Traits\HasSeoFields;
use App\Modules\Shop\Models\Category;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;

class CategoryResource extends Resource
{
    use HasSeoFields;
    protected static ?string $model = Category::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

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
                    ->columnSpan(6),
                Select::make('parent_id')
                    ->options(
                        Category::defaultOrder()
                            ->withDepth()
                            ->get()
                            ->toFlatTree()
                            ->mapWithKeys(fn ($category) => [
                                $category->id => str_repeat('— ', $category->depth) . $category->title
                            ])
                    )
                    ->disableOptionWhen(function ($value, $record) {
                        if (!$record) return false;

                        return $value == $record->id ||
                            $record->descendants->pluck('id')->contains($value);
                    })
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->columnSpan(6),
                Select::make('_position_id')
                    ->options(function (callable $get, $record) {
                        $parentId = $get('parent_id');
                        return Category::query()
                            ->when($parentId,
                                fn ($q) => $q->where('parent_id', $parentId),
                                fn ($q) => $q->whereNull('parent_id')
                            )
                            ->defaultOrder()
                            ->get()
                            ->mapWithKeys(fn ($c) => [
                                $c->id => $c->title
                            ]);
                    })
                    ->afterStateHydrated(function ($state, callable $set, $record) {
                        if ($record && !$state) {
                            $set('_position_id', $record->id);
                        }
                    })
                    ->searchable()
                    ->preload()
                    ->live()
                    ->nullable()
                    ->columnSpan(6),

                FileUpload::make('img')
                    ->image()
                    ->columnSpan(6)
                    ->visibility('public')
                    ->getUploadedFileNameForStorageUsing(function ($file, $record) {
                        return ($record ?? new Category())->getFilePath(
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
            ->modifyQueryUsing(fn ($query) =>
                $query->withDepth()->defaultOrder()
            )
            ->defaultSort('_lft')
            ->columns([
                ImageColumn::make('img'),
                TextColumn::make('title')
                    ->formatStateUsing(fn ($record) =>
                        str_repeat('— ', $record->depth) . $record->title
                    )
                    ->searchable(),
                TextColumn::make('slug')
                    ->searchable(),
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
                //
            ])
            ->recordActions([
                /*ViewAction::make()
                    ->iconButton()
                    ->url(fn ($record) => route('news.show',['slug'=>$record->slug]))
                    ->openUrlInNewTab(),*/
                BaseEditAction::make()->iconButton(),
                DeleteAction::make()
                    ->iconButton()
                    ->disabled(fn ($record) =>
                        $record->children()->exists() ||
                        $record->products()->exists()
                    )
                    ->tooltip(fn ($record) => (
                        $record->children()->exists() || $record->products()->exists()
                    )
                        ? __('admin.messages.delete_child_items_forbidden')
                        : null
                    ),
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
            'index' => ManageCategories::route('/'),
        ];
    }
}
