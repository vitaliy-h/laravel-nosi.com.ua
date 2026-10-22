<?php

namespace App\Filament\Resources\Home\Promos;

use App\Filament\Actions\BaseEditAction;
use App\Filament\Resources\Home\Promos\Pages\ManagePromos;
use App\Filament\Tables\Columns\InlineEditColumn;
use App\Modules\Promo\Enums\PromoStyleEnum;
use App\Modules\Promo\Models\Promo;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

/**
 * Home page promo grid. Up to three visible banners are shown, the grid lays
 * itself out by their count.
 */
class PromoResource extends Resource
{
    protected static ?string $model = Promo::class;

    public static function getModelLabel(): string
    {
        return __('admin.models.promo');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.tables.promos');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(12)
            ->components([
                TextInput::make('title')
                    ->columnSpan(8),
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
                TextInput::make('big_text')
                    ->label(__('admin.fields.big_text'))
                    ->columnSpan(4),
                Select::make('style')
                    ->label(__('admin.fields.style'))
                    ->options(PromoStyleEnum::options())
                    ->default(PromoStyleEnum::PLUM->value)
                    ->required()
                    ->columnSpan(4),

                TextInput::make('button_text')
                    ->label(__('admin.fields.button_text'))
                    ->columnSpan(4),
                TextInput::make('url')
                    ->columnSpan(8),

                FileUpload::make('img')
                    ->image()
                    ->columnSpan(6)
                    ->visibility('public')
                    ->getUploadedFileNameForStorageUsing(function ($file, $record) {
                        return ($record ?? new Promo)->getFilePath(
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
                    ->rules(['nullable', 'max:255'])
                    ->searchable(),
                TextColumn::make('big_text')
                    ->label(__('admin.fields.big_text')),
                TextColumn::make('style')
                    ->label(__('admin.fields.style'))
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state?->label()),
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
            ->filters([
                SelectFilter::make('style')
                    ->label(__('admin.fields.style'))
                    ->options(PromoStyleEnum::options()),
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
            'index' => ManagePromos::route('/'),
        ];
    }
}
