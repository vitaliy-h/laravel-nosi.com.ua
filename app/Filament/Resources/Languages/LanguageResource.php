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

class LanguageResource extends Resource
{
    protected static ?string $model = Language::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label(__('admin.fields.title')),
                Select::make('localization')
                    ->label(__('admin.fields.localization'))
                    ->options(Localization::options())
                    ->required()
                    ->unique(),
                TextInput::make('rank')
                    ->label(__('admin.fields.rank'))
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('display')
                    ->label(__('admin.fields.display'))
                    ->inline(false)
                    ->default(true),
                Section::make(__('admin.fields.dictionary'))
                    ->columns(2)
                    ->schema(static::getTranslationFields())
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('rank','DESC')
            ->columns([
                TextColumn::make('title')
                    ->label(__('admin.fields.title'))
                    ->searchable(),
                TextColumn::make('localization')
                    ->label(__('admin.fields.localization'))
                    ->searchable()
                    ->badge()
                    ->formatStateUsing(fn($state) => $state?->label()),
                TextInputColumn::make('rank')
                    ->label(__('admin.fields.rank'))
                    ->type('number')
                    ->sortable()
                    ->width('1%'),
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

    public static function getTranslationFields(): array
    {
        $translations = require lang_path('en/dictionary.php');

        $fields = [];

        foreach ($translations as $key => $value) {
            $fields[] = TextInput::make("dictionary.$key")
                ->label($key)
                ->default($value);
        }

        return $fields;
    }
}
