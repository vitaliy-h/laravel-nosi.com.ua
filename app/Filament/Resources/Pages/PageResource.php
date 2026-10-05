<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Actions\BaseEditAction;
use App\Filament\Builder\Blocks\GalleryBlock;
use App\Filament\Builder\Blocks\ImageBlock;
use App\Filament\Builder\Blocks\TeasersBlock;
use App\Filament\Builder\Blocks\TextBlock;
use App\Filament\Builder\Blocks\VideoBlock;
use App\Filament\Resources\Pages\Pages\ManagePages;
use App\Filament\Traits\HasSeoFields;
use App\Modules\Core\Support\UrlHelper;
use App\Modules\Page\Enums\PageModule;
use App\Modules\Page\Models\Page;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Table;

class PageResource extends Resource
{
    use HasSeoFields;

    protected static ?string $model = Page::class;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(12)
            ->components([
                TextInput::make('title')
                    ->columnSpan(6)
                    ->required(),
                TextInput::make('rank')
                    ->numeric()
                    ->columnSpan(2)
                    ->default(1),
                Toggle::make('display')
                    ->inline(false)
                    ->default(true),

                Select::make('module')
                    ->columnSpan(6)
                    ->options(PageModule::activeOptions())
                    ->required()
                    ->default(PageModule::TEXT->value)
                    ->disabled(fn (): bool => !auth()->user()?->isSuperAdmin())
                    ->dehydrated(),
                Toggle::make('display_menu')
                    ->columnSpan(3)
                    ->inline(false),
                Toggle::make('display_menu_footer')
                    ->columnSpan(3)
                    ->inline(false),

                Builder::make('content')
                    ->blocks([
                        TextBlock::make(),
                        ImageBlock::make(),
                        GalleryBlock::make(),
                        VideoBlock::make(),
                        TeasersBlock::make(),
                    ])
                    ->collapsible()
                    ->cloneable()
                    ->reorderable()
                    ->columnSpanFull()
                    ->default([
                        [
                            'type' => 'text',
                            'data' => [
                                'style' => 'default',
                            ],
                        ],
                    ]),

                static::getSeoSection(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('rank', 'DESC')
            ->columns([
                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('slug')
                    ->searchable(),
                TextColumn::make('module')
                    ->searchable()
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state?->label()),
                TextInputColumn::make('rank')
                    ->type('number')
                    ->sortable()
                    ->alignEnd()
                    ->width('1%')
                    ->extraAttributes(['style' => 'min-width: 0; width: 7rem']),
                ToggleColumn::make('display_menu'),
                ToggleColumn::make('display_menu_footer'),
                ToggleColumn::make('display'),
                ToggleColumn::make('is_indexable'),
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
                //
            ])
            ->recordActions([
                ViewAction::make()
                    ->iconButton()
                    ->url(fn ($record) => UrlHelper::normalizeForLink($record->slug))
                    ->openUrlInNewTab(),
                BaseEditAction::make()->iconButton(),
                DeleteAction::make()
                    ->iconButton()
                    ->disabled(fn ($record) => $record->module !== PageModule::TEXT && !auth()->user()?->isSuperAdmin())
                    ->tooltip(fn ($record) => $record->module !== PageModule::TEXT && !auth()->user()?->isSuperAdmin()
                        ? __('admin.messages.page_delete_forbidden')
                        : null
                    ),
            ], position: RecordActionsPosition::BeforeColumns)
            ->toolbarActions([
                CreateAction::make(),
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->action(function ($records) {
                            $isSuperAdmin = auth()->user()?->isSuperAdmin();

                            $records
                                ->filter(fn ($record) => $isSuperAdmin || $record->module === PageModule::TEXT)
                                ->each
                                ->delete();
                        }),
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
