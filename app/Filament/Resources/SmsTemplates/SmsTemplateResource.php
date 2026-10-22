<?php

namespace App\Filament\Resources\SmsTemplates;

use App\Filament\Actions\BaseEditAction;
use App\Filament\Resources\SmsTemplates\Pages\ManageSmsTemplates;
use App\Modules\Notification\Enums\SmsEnum;
use App\Modules\Notification\Models\SmsTemplate;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Table;

class SmsTemplateResource extends Resource
{
    protected static ?string $model = SmsTemplate::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    public static function getModelLabel(): string
    {
        return __('admin.models.sms_template');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.tables.sms_templates');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('key')
                    ->options(SmsEnum::options())
                    ->required()
                    ->searchable()
                    ->unique()
                    ->disabledOn('edit'),

                TextInput::make('subject')
                    ->columnSpanFull(),

                Textarea::make('body')
                    ->columnSpanFull()
                    ->rows(5),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('key')
            ->columns([
                TextColumn::make('key')
                    ->badge()
                    ->searchable()
                    ->sortable(),

                TextColumn::make('subject')
                    ->searchable()
                    ->limit(50),

                TextColumn::make('body')
                    ->limit(60)
                    ->tooltip(fn ($record) => $record->body)
                    ->searchable(),

                TextColumn::make('updated_at')
                    ->since()
                    ->sortable(),
            ])
            ->filters([])
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
            'index' => ManageSmsTemplates::route('/'),
        ];
    }
}
