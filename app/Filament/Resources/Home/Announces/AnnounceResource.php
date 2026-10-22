<?php

namespace App\Filament\Resources\Home\Announces;

use App\Filament\Actions\BaseEditAction;
use App\Filament\Resources\Home\Announces\Pages\ManageAnnounces;
use App\Filament\Tables\Columns\InlineEditColumn;
use App\Modules\Announce\Models\Announce;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Table;

class AnnounceResource extends Resource
{
    protected static ?string $model = Announce::class;

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
                TextInput::make('url')
                    ->columnSpan(8),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('rank', 'desc')
            ->columns([
                InlineEditColumn::make('title')
                    ->rules(['required', 'max:255'])
                    ->searchable(),
                InlineEditColumn::make('url')
                    ->rules(['nullable', 'max:255']),
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
            'index' => ManageAnnounces::route('/'),
        ];
    }
}
