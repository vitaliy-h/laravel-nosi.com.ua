<?php

namespace App\Filament\Resources\Shop\Categories;

use App\Filament\Actions\BaseEditAction;
use App\Filament\Resources\Shop\Categories\Pages\ManageCategories;
use App\Models\Shop\Category;
use App\Modules\News\Models\News;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
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

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

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
                    ->required()
                    ->columnSpan(3),
                TextInput::make('rank')
                    ->label(__('admin.fields.rank'))
                    ->required()
                    ->numeric()
                    ->columnSpan(3)
                    ->default(0),
                Toggle::make('display')
                    ->label(__('admin.fields.display'))
                    ->default(true)
                    ->inline(false),
                FileUpload::make('img')
                    ->label(__('admin.fields.img'))
                    ->image()
                    ->columnSpan(6)
                    ->visibility('public')
                    ->getUploadedFileNameForStorageUsing(function ($file, $record) {
                        return ($record ?? new News())->getFilePath(
                            $file->getClientOriginalName(),
                            'img',
                        );
                    }),
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
            ->defaultSort('rank', 'desc')
            ->columns([
                ImageColumn::make('img')
                    ->label(__('admin.fields.img')),
                TextColumn::make('title')
                    ->label(__('admin.fields.title'))
                    ->searchable(),
                TextColumn::make('slug')
                    ->label(__('admin.fields.slug'))
                    ->searchable(),
                TextColumn::make('rank')
                    ->label(__('admin.fields.rank'))
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
                /*ViewAction::make()
                    ->iconButton()
                    ->url(fn ($record) => route('news.show',['slug'=>$record->slug]))
                    ->openUrlInNewTab(),*/
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
            'index' => ManageCategories::route('/'),
        ];
    }
}
