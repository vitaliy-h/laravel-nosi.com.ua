<?php

namespace App\Filament\Resources\News;

use App\Filament\Actions\BaseEditAction;
use App\Filament\Resources\News\Pages\ManageNews;
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
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class NewsResource extends Resource
{
    protected static ?string $model = News::class;

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
                    ->unique()
                    ->columnSpan(3),
                DateTimePicker::make('published_at')
                    ->label(__('admin.fields.published_at'))
                    ->columnSpan(3),
                Toggle::make('display')
                    ->label(__('admin.fields.display'))
                    ->inline(false),
                Textarea::make('short_text')
                    ->label(__('admin.fields.short_text'))
                    ->columnSpan(6),
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
                    ->required()
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
            ->defaultSort('published_at', 'desc')
            ->columns([
                ImageColumn::make('img')
                    ->label(__('admin.fields.img')),
                TextColumn::make('title')
                    ->label(__('admin.fields.title'))
                    ->searchable(),
                TextColumn::make('slug')
                    ->label(__('admin.fields.slug'))
                    ->searchable(),
                TextColumn::make('published_at')
                    ->label(__('admin.fields.published_at'))
                    ->dateTime()
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
                ViewAction::make()
                    ->iconButton()
                    ->url(fn ($record) => route('news.show',['slug'=>$record->slug]))
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
            'index' => ManageNews::route('/'),
        ];
    }
}
