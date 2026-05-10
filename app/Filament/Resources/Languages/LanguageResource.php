<?php

namespace App\Filament\Resources\Languages;

use App\Filament\Actions\BaseEditAction;
use App\Filament\Resources\Languages\Pages\ManageLanguages;
use App\Modules\Core\Enums\Localization;
use App\Modules\Core\Models\Language;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Filament\Schemas\Components\Utilities\Get;

class LanguageResource extends Resource
{
    protected static ?string $model = Language::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(12)
            ->components([
                TextInput::make('title')
                    ->columnSpan(4),
                Select::make('localization')
                    ->options(Localization::options())
                    ->required()
                    ->columnSpan(4)
                    ->unique(),
                TextInput::make('rank')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->columnSpan(2),
                Toggle::make('display')
                    ->inline(false)
                    ->default(true),
                Section::make(__('admin.fields.dictionary'))
                    ->columns(2)
                    ->schema(fn (Get $get) => static::getTranslationFields($get))
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('rank','DESC')
            ->columns([
                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('localization')
                    ->searchable()
                    ->badge()
                    ->formatStateUsing(fn($state) => $state?->label()),
                TextInputColumn::make('rank')
                    ->type('number')
                    ->sortable()
                    ->width('1%'),
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
            'index' => ManageLanguages::route('/'),
        ];
    }

    public static function getTranslationFields(Get $get): array
    {
        $locale = $get('localization') ?: 'en';

        $translations = require lang_path($locale . '/dictionary.php');

        $fields = [];

        foreach ($translations as $key => $value) {
            $fields[] = TextInput::make("dictionary.$key")
                ->label($key)
                ->default($value);
        }

        return $fields;
    }
}
