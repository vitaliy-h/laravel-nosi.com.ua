<?php

namespace App\Filament\Resources\Home\Sliders;

use App\Filament\Actions\BaseEditAction;
use App\Filament\Resources\Home\Sliders\Pages\ManageSliders;
use App\Filament\Tables\Columns\InlineEditColumn;
use App\Modules\Slider\Models\Slider;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Table;

class SliderResource extends Resource
{
    protected static ?string $model = Slider::class;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(12)
            ->components([
                TextInput::make('title')
                    ->columnSpan(8)
                    ->required(),
                TextInput::make('rank')
                    ->numeric()
                    ->columnSpan(2)
                    ->default(1),
                Toggle::make('display')
                    ->columnSpan(2)
                    ->inline(false)
                    ->default(true),

                TextInput::make('kicker')
                    ->label(__('admin.fields.kicker'))
                    ->columnSpan(4),
                TextInput::make('button_text')
                    ->label(__('admin.fields.button_text'))
                    ->columnSpan(4),
                TextInput::make('url')
                    ->columnSpan(4),

                FileUpload::make('img')
                    ->image()
                    ->required()
                    ->columnSpan(6)
                    ->visibility('public')
                    ->getUploadedFileNameForStorageUsing(function ($file, $record) {
                        return ($record ?? new Slider)->getFilePath(
                            $file->getClientOriginalName(),
                            'img',
                        );
                    }),
                Textarea::make('text')
                    ->rows(5)
                    ->columnSpan(6),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('rank', 'desc')
            ->columns([
                ImageColumn::make('img'),
                InlineEditColumn::make('title')
                    ->rules(['required', 'max:255'])
                    ->searchable(),
                TextColumn::make('kicker')
                    ->label(__('admin.fields.kicker')),
                InlineEditColumn::make('rank')
                    ->editAsNumber()
                    ->rules(['required', 'integer'])
                    ->sortable()
                    ->alignEnd(),
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
            ->filters([])
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
            'index' => ManageSliders::route('/'),
        ];
    }
}
