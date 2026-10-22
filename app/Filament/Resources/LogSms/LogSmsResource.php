<?php

namespace App\Filament\Resources\LogSms;

use App\Filament\Resources\LogSms\Pages\ManageLogSms;
use App\Modules\Notification\Enums\LogSmsStatusEnum;
use App\Modules\Notification\Enums\NotificationSourceEnum;
use App\Modules\Notification\Enums\SmsEnum;
use App\Modules\Notification\Models\LogSms;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LogSmsResource extends Resource
{
    protected static ?string $model = LogSms::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleBottomCenterText;

    public static function getModelLabel(): string
    {
        return __('admin.models.log_sms');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.tables.log_sms');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('template_key')
                    ->options(SmsEnum::options()),
                TextInput::make('receiver')
                    ->required(),
                Textarea::make('body')
                    ->columnSpanFull(),
                Select::make('status')
                    ->options(LogSmsStatusEnum::options())
                    ->required(),
                Textarea::make('error_message')
                    ->columnSpanFull(),
                Select::make('source')
                    ->options(NotificationSourceEnum::options()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'DESC')
            ->columns([
                TextColumn::make('template_key')
                    ->badge()
                    ->searchable()
                    ->sortable(),

                TextColumn::make('receiver')
                    ->searchable()
                    ->limit(30),

                TextColumn::make('body')
                    ->label(__('admin.fields.content'))
                    ->searchable()
                    ->limit(50),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn ($state) => $state->color()),

                TextColumn::make('source')
                    ->badge(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('admin.fields.status'))
                    ->options(LogSmsStatusEnum::options()),

                SelectFilter::make('source')
                    ->label(__('admin.fields.source'))
                    ->options(NotificationSourceEnum::options()),

                SelectFilter::make('template_key')
                    ->label(__('admin.fields.template_key'))
                    ->options(SmsEnum::options()),

                Filter::make('created_at')
                    ->label(__('admin.fields.date'))
                    ->form([
                        DatePicker::make('from')->label(__('admin.fields.date_from')),
                        DatePicker::make('until')->label(__('admin.fields.date_until')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
                            ->when($data['until'], fn ($q, $v) => $q->whereDate('created_at', '<=', $v));
                    }),
            ])
            ->recordActions([
                ViewAction::make()
                    ->iconButton()
                    ->modalWidth('3xl')
                    ->slideOver()
                    ->schema(self::getViewSchema()),
                DeleteAction::make()->iconButton(),
            ], position: RecordActionsPosition::BeforeColumns)
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageLogSms::route('/'),
        ];
    }

    private static function getViewSchema(): array
    {
        return [
            Section::make()
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextEntry::make('template_key')->badge(),
                            TextEntry::make('source')->badge(),
                            TextEntry::make('receiver'),
                            TextEntry::make('status')
                                ->badge()
                                ->color(fn ($state) => $state->color()),
                            TextEntry::make('created_at')->dateTime(),
                        ]),
                ]),
            Section::make()
                ->schema([
                    TextEntry::make('error_message')->columnSpanFull(),
                ])
                ->visible(fn ($record) => !empty($record->error_message)),
            Section::make(__('admin.fields.content'))
                ->schema([
                    TextEntry::make('body')->columnSpanFull(),
                ]),
        ];
    }
}
