<?php

namespace App\Filament\Resources\LetterTemplates;

use App\Filament\Actions\BaseEditAction;
use App\Filament\Resources\LetterTemplates\Pages\ManageLetterTemplates;
use App\Modules\Core\Enums\LetterEnum;
use App\Modules\Core\Models\LetterTemplate;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LetterTemplateResource extends Resource
{
    protected static ?string $model = LetterTemplate::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([

                Select::make('key')
                    ->options(LetterEnum::class)
                    ->required()
                    ->searchable()
                    ->unique()
                    ->disabledOn('edit'),

                TextInput::make('sender')
                    ->email()
                    ->placeholder('noreply@example.com'),

                TextInput::make('sender_name')
                    ->placeholder('Company Name'),

                TextInput::make('receivers')
                    ->placeholder('admin@example.com, test@example.com')
                    ->helperText(__('admin.fields.letter_receiver_helper')),

                TextInput::make('subject')
                    ->required()
                    ->columnSpanFull(),

                RichEditor::make('body')
                    ->columnSpanFull()
                    ->extraInputAttributes([
                        'style' => 'font-family: monospace',
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('key')
                    ->badge()
                    ->searchable()
                    ->sortable(),

                TextColumn::make('sender')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('receivers')
                    ->limit(40)
                    ->tooltip(fn($record) => $record->receivers)
                    ->searchable(),

                TextColumn::make('subject')
                    ->searchable()
                    ->limit(50),

                TextColumn::make('updated_at')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
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
            'index' => ManageLetterTemplates::route('/'),
        ];
    }
}
