<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Actions\BaseEditAction;
use App\Filament\Resources\Pages\Pages\ManagePages;
use App\Modules\Core\Enums\PageModule;
use App\Modules\Core\Facades\UrlFacade;
use App\Modules\Core\Models\Page;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Table;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(12)
            ->components([
                TextInput::make('title')
                    ->columnSpan(6)
                    ->required(),
                TextInput::make('slug')
                    ->columnSpan(3)
                    ->unique()
                    ->afterStateUpdated(fn($state, callable $set) => $set('slug', UrlFacade::normalize($state)))
                    ->required()
                    ->disabled(fn($record) => $record && $record->module !== PageModule::TEXT)
                    /*
                    ->helperText(fn($record) => $record && $record->module !== PageModule::TEXT
                        ? __('admin.fields.url_helper')
                        : null
                    )*/,
                TextInput::make('rank')
                    ->numeric()
                    ->columnSpan(2)
                    ->default(0),
                Toggle::make('display')
                    ->inline(false)
                    ->default(true),

                Select::make('module')
                    ->columnSpan(6)
                    ->options(PageModule::options())
                    ->required()
                    ->default(PageModule::TEXT->value)
                    ->disabled(fn($record) => $record !== null)
                    ->dehydrated()
                    ->visible(fn($record) => $record !== null),
                Toggle::make('display_menu')
                    ->columnSpan(3)
                    ->inline(false),
                Toggle::make('display_menu_footer')
                    ->columnSpan(3)
                    ->inline(false),

                RichEditor::make('text')
                    ->columnSpanFull(),
                TextInput::make('meta_title')
                    ->columnSpanFull(),
                Textarea::make('meta_description')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('slug')
                    ->searchable(),
                TextColumn::make('module')
                    ->searchable()
                    ->badge()
                    ->formatStateUsing(fn($state) => $state?->label()),
                TextInputColumn::make('rank')
                    ->type('number')
                    ->sortable()
                    ->alignEnd()
                    ->width('100px'),
                ToggleColumn::make('display_menu'),
                ToggleColumn::make('display_menu_footer'),
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
                    ->url(fn($record) => $record->slug)
                    ->openUrlInNewTab(),
                BaseEditAction::make()->iconButton(),
                DeleteAction::make()
                    ->iconButton()
                    ->disabled(fn($record) => $record->module !== PageModule::TEXT)
                    ->tooltip(fn($record) =>
                    $record->module !== PageModule::TEXT
                        ? __('admin.messages.page_delete_forbidden')
                        : null
                    ),
            ], position: \Filament\Tables\Enums\RecordActionsPosition::BeforeColumns)
            ->toolbarActions([
                \Filament\Actions\CreateAction::make(),
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->action(function ($records) {
                            $records
                                ->filter(fn($record) => $record->module === PageModule::TEXT)
                                ->each
                                ->delete();
                        })
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManagePages::route('/'),
        ];
    }
}
