<?php

namespace App\Filament\Resources\Languages;

use App\Filament\Actions\BaseEditAction;
use App\Filament\Resources\Languages\Pages\ManageLanguages;
use App\Modules\Translation\Enums\Localization;
use App\Modules\Translation\Models\Language;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Table;

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
                    ->unique()
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set, $record) {

                        if ($record) {
                            return;
                        }

                        $translations = static::getDictionaryTemplate(
                            $state
                        );

                        $dictionary = static::normalizeDictionaryTemplate(
                            $translations
                        );

                        $set('dictionary', $dictionary);
                    }),
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
                    ->key(fn (Get $get) => 'dictionary-' . ($get('localization') ?? Localization::EN->value))
                    ->columnSpanFull()
                    ->contained(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('rank', 'DESC')
            ->columns([
                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('localization')
                    ->searchable()
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state?->label()),
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
            'index' => ManageLanguages::route('/'),
        ];
    }

    public static function getTranslationFields(Get $get): array
    {
        $locale = $get('localization') ?: Localization::EN->value;

        $translations = static::getDictionaryTemplate($locale);

        $sections = [];

        $first = true;

        foreach ($translations as $module => $fields) {

            $schema = [];
            $currentRow = [];

            foreach ($fields as $rawKey => $value) {

                $config = static::parseFieldKey($rawKey);

                $field = static::makeTranslationField(
                    module: $module,
                    key: $config['key'],
                    value: $value,
                    type: $config['type'],
                    span: $config['span'],
                );

                $currentRow[] = $field;

                // start new row
                if ($config['br']) {

                    $schema[] = Grid::make(12)
                        ->schema($currentRow);

                    $currentRow = [];
                }
            }

            if (count($currentRow)) {
                $schema[] = Grid::make(12)
                    ->schema($currentRow);
            }

            $sections[] = Section::make(str($module)->title())
                ->schema($schema)
                ->collapsible()
                ->collapsed(!$first)
                ->columnSpanFull()
                ->contained(false);

            $first = false;
        }

        return $sections;
    }

    protected static function getDictionaryTemplate(string $locale): array
    {

        $path = lang_path($locale . '/dictionary.php');

        // exact locale
        if (file_exists($path)) {
            return require $path;
        }

        // fallback to english
        $fallbackPath = lang_path(Localization::EN->value . '/dictionary.php');

        if (file_exists($fallbackPath)) {
            return require $fallbackPath;
        }

        return [];
    }

    protected static function parseFieldKey(string $rawKey): array
    {

        $parts = explode(':', $rawKey);

        $key = array_shift($parts);

        $type = 'input';
        $span = 4;
        $br = false;

        foreach ($parts as $part) {

            if ($part === 'textarea') {
                $type = 'textarea';
                $span = 12;
            }

            if ($part === 'html') {
                $type = 'html';
                $span = 12;
            }

            if ($part === 'br') {
                $br = true;
            }

            if (str_starts_with($part, 'input')) {
                $span = (int) str_replace('input', '', $part);
            }
        }

        return [
            'key'  => $key,
            'type' => $type,
            'span' => $span,
            'br'   => $br,
        ];
    }

    protected static function makeTranslationField(string $module, string $key, string $value, string $type, int $span)
    {

        $path = "dictionary.{$module}.{$key}";

        $field = match ($type) {

            'textarea' => Textarea::make($path),

            'html' => RichEditor::make($path),

            default => TextInput::make($path),
        };

        return $field
            ->label(str($key)->replace('_', ' ')->title())
            ->default($value)
            ->columnSpan($span);
    }

    protected static function normalizeDictionaryTemplate(
        array $dictionary,
    ): array {

        $normalized = [];

        foreach ($dictionary as $key => $value) {

            $cleanKey = explode(':', $key)[0];

            if (is_array($value)) {

                $normalized[$cleanKey] = static::normalizeDictionaryTemplate(
                    $value
                );

                continue;
            }

            $normalized[$cleanKey] = $value;
        }

        return $normalized;
    }
}
