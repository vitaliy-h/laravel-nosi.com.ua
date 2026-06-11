<?php

namespace App\Filament\Resources\News;

use App\Filament\Actions\BaseEditAction;
use App\Filament\Resources\News\Pages\ManageNews;
use App\Filament\Traits\HasSeoFields;
use App\Modules\Core\Enums\PageModule;
use App\Modules\News\Models\News;
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
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class NewsResource extends Resource
{
    use HasSeoFields;

    protected static ?string $model = News::class;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(12)
            ->components([
                TextInput::make('title')
                    ->columnSpan(6)
                    ->required(),
                DateTimePicker::make('published_at')
                    ->columnSpan(3),
                Toggle::make('display')
                    ->inline(false)
                    ->default(true),
                Textarea::make('short_text')
                    ->columnSpan(6),
                FileUpload::make('img')
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
                    ->columnSpanFull(),
                static::getSeoSection(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('published_at', 'desc')
            ->columns([
                ImageColumn::make('img'),
                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('slug')
                    ->searchable(),
                TextColumn::make('published_at')
                    ->dateTime()
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
                //
            ])
            ->recordActions([
                ViewAction::make()
                    ->iconButton()
                    ->url(fn($record) => module_url('news', [$record->slug]))
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
