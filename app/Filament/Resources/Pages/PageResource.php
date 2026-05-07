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
                    ->label(__('admin.fields.title'))
                    ->columnSpan(6)
                    ->required(),
                TextInput::make('slug')
                    ->label(__('admin.fields.slug'))
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
                    ->label(__('admin.fields.rank'))
                    ->required()
                    ->numeric()
                    ->columnSpan(2)
                    ->default(0),
                Toggle::make('display')
                    ->label(__('admin.fields.display'))
                    ->inline(false),

                Select::make('module')
                    ->label(__('admin.fields.module'))
                    ->columnSpan(6)
                    ->options(PageModule::options())
                    ->required()
                    ->default(PageModule::TEXT->value)
                    ->disabled(fn($record) => $record !== null)
                    ->dehydrated()
                    ->visible(fn($record) => $record !== null),
                Toggle::make('display_menu')
                    ->label(__('admin.fields.display_menu'))
                    ->inline(false),
                Toggle::make('display_menu_footer')
                    ->label(__('admin.fields.display_menu_footer'))
                    ->inline(false),

                RichEditor::make('text')
                    ->label(__('admin.fields.text'))
                    ->columnSpanFull(),
                TextInput::make('meta_title')
                    ->label(__('admin.fields.meta_title'))
                    ->columnSpanFull(),
                Textarea::make('meta_description')
                    ->label(__('admin.fields.meta_description'))
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label(__('admin.fields.title'))
                    ->searchable(),
                TextColumn::make('slug')
                    ->label(__('admin.fields.slug'))
                    ->searchable(),
                TextColumn::make('module')
                    ->label(__('admin.fields.module'))
                    ->searchable()
                    ->badge()
                    ->formatStateUsing(fn($state) => $state?->label()),
                TextInputColumn::make('rank')
                    ->label(__('admin.fields.rank'))
                    ->type('number')
                    ->sortable(),
                ToggleColumn::make('display_menu')
                    ->label(__('admin.fields.display_menu')),
                ToggleColumn::make('display_menu_footer')
                    ->label(__('admin.fields.display_menu_footer')),
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
                ViewAction::make()
                    ->iconButton()
                    ->url(fn($record) => $record->slug)
                    ->openUrlInNewTab(),
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
