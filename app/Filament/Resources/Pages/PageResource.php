<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Actions\BaseEditAction;
use App\Filament\Resources\Pages\Pages\ManagePages;
use App\Modules\Core\Enums\PageModule;
use App\Modules\Core\Facades\UrlFacade;
use App\Modules\Core\Models\Page;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('admin.pages.fields.name'))
                    ->required(),
                TextInput::make('url')
                    ->label(__('admin.pages.fields.url'))
                    ->unique()
                    ->afterStateUpdated(fn($state, callable $set) => $set('url', UrlFacade::normalize($state)))
                    ->required()
                    ->disabled(fn($record) => $record && $record->module !== PageModule::TEXT
                    )
                    ->helperText(fn($record) => $record && $record->module !== PageModule::TEXT
                        ? __('admin.pages.fields.url_helper')
                        : null
                    ),
                Select::make('module')
                    ->label(__('admin.pages.fields.module'))
                    ->options(PageModule::options())
                    ->required()
                    ->default(PageModule::TEXT->value)
                    ->disabled(fn($record) => $record !== null)
                    ->dehydrated()
                    ->visible(fn($record) => $record !== null),
                TextInput::make('rank')
                    ->label(__('admin.pages.fields.rank'))
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('display')
                    ->label(__('admin.pages.fields.display'))
                    ->required(),
                Toggle::make('display_menu')
                    ->label(__('admin.pages.fields.display_menu'))
                    ->required(),
                Toggle::make('display_menu_footer')
                    ->label(__('admin.pages.fields.display_menu_footer'))
                    ->required(),
                RichEditor::make('text')
                    ->label(__('admin.pages.fields.text'))
                    ->columnSpanFull(),
                TextInput::make('meta_title')
                    ->label(__('admin.pages.fields.meta_title'))
                    ->columnSpanFull(),
                Textarea::make('meta_description')
                    ->label(__('admin.pages.fields.meta_description'))
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('admin.pages.fields.name'))
                    ->searchable(),
                TextColumn::make('url')
                    ->label(__('admin.pages.fields.url'))
                    ->searchable(),
                TextColumn::make('module')
                    ->label(__('admin.pages.fields.module'))
                    ->searchable()
                    ->badge()
                    ->formatStateUsing(fn($state) => $state?->label()),
                TextColumn::make('rank')
                    ->label(__('admin.pages.fields.rank'))
                    ->numeric()
                    ->sortable(),
                IconColumn::make('display')
                    ->label(__('admin.pages.fields.display'))
                    ->boolean(),
                IconColumn::make('display_menu')
                    ->label(__('admin.pages.fields.display_menu'))
                    ->boolean(),
                IconColumn::make('display_menu_footer')
                    ->label(__('admin.pages.fields.display_menu_footer'))
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label(__('admin.core.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('admin.core.fields.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                BaseEditAction::make()->iconButton(),
                DeleteAction::make()->iconButton()->visible(fn($record) => $record->module === PageModule::TEXT
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
