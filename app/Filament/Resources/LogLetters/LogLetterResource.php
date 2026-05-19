<?php

namespace App\Filament\Resources\LogLetters;

use App\Filament\Resources\LogLetters\Pages\ManageLogLetters;
use App\Modules\Core\Enums\LetterEnum;
use App\Modules\Core\Enums\LogLetterSourceEnum;
use App\Modules\Core\Enums\LogLetterStatusEnum;
use App\Modules\Core\Models\LogLetter;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LogLetterResource extends Resource
{
    protected static ?string $model = LogLetter::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('template_key')
                    ->options(LetterEnum::class),
                TextInput::make('receiver')
                    ->required(),
                TextInput::make('subject'),
                TextInput::make('sender'),
                TextInput::make('sender_name'),
                Textarea::make('body')
                    ->columnSpanFull(),
                Textarea::make('content')
                    ->columnSpanFull(),
                Select::make('status')
                    ->options(LogLetterStatusEnum::class)
                    ->required(),
                Textarea::make('error_message')
                    ->columnSpanFull(),
                Select::make('source')
                    ->options(LogLetterSourceEnum::class),
                TextInput::make('attachments'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at','DESC')
            ->columns([
                TextColumn::make('template_key')
                    ->badge()
                    ->searchable()
                    ->sortable(),

                TextColumn::make('receiver')
                    ->searchable()
                    ->limit(30),

                TextColumn::make('subject')
                    ->searchable()
                    ->limit(40),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        LogLetterStatusEnum::SENT => 'success',
                        LogLetterStatusEnum::FAILED => 'danger',
                        default => 'warning',
                    }),

                TextColumn::make('source')
                    ->badge(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('admin.fields.status'))
                    ->options(LogLetterStatusEnum::class),

                SelectFilter::make('source')
                    ->label(__('admin.fields.source'))
                    ->options(LogLetterSourceEnum::class),

                SelectFilter::make('template_key')
                    ->label(__('admin.fields.template_key'))
                    ->options(LetterEnum::class),
            ])
            ->recordActions([
                ViewAction::make()
                    ->iconButton()
                    ->modalWidth('5xl')
                    ->slideOver()
                    ->schema(self::getViewSchema()),
                DeleteAction::make()->iconButton(),
            ], position: \Filament\Tables\Enums\RecordActionsPosition::BeforeColumns)
            ->toolbarActions([
//                \Filament\Actions\CreateAction::make(),
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageLogLetters::route('/'),
        ];
    }

    private static function getViewSchema(): array
    {
        return [
            Section::make()
                ->schema([

                    Grid::make(2)
                        ->schema([
                            TextEntry::make('template_key')
                                ->badge(),
                            TextEntry::make('source')->badge(),
                            TextEntry::make('receiver'),

                            TextEntry::make('sender'),
                            TextEntry::make('sender_name'),
                            TextEntry::make('status')
                                ->badge()
                                ->color(fn($state) => match ($state) {
                                LogLetterStatusEnum::SENT => 'success',
                                LogLetterStatusEnum::FAILED => 'danger',
                                default => 'warning',
                            }),

                            TextEntry::make('created_at')->dateTime(),
                        ]),
                ]),
            Section::make()
                ->schema([

                    TextEntry::make('error_message')
                        ->columnSpanFull(),

                ])
                ->visible(fn ($record) => !empty($record->error_message)),
            Section::make()
                ->schema([
                    TextInput::make('subject')->disabled()->columnSpanFull(),
                    Textarea::make('body')
                        ->disabled()
                        ->columnSpanFull()
                        ->rows(6),
                ]),

            Section::make(__('admin.fields.content'))
                ->schema([
                    ViewField::make('content_view')
                        ->view('filament.log_letter.email-preview'),
                ]),

            Section::make()
                ->schema([

                    FileUpload::make('attachments')
                        ->label(__('admin.fields.attachments'))
                        ->multiple()
                        ->disabled()
                        ->downloadable()
                        ->openable()
                        ->columnSpanFull(),

                ])
                ->visible(fn ($record) => !empty($record->attachments)),
        ];
    }
}
