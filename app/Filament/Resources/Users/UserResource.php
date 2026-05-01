<?php

namespace App\Filament\Resources\Users;

use App\Filament\Actions\BaseEditAction;
use App\Filament\Resources\Users\Pages\ManageUsers;
use App\Models\User;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('admin.users.fields.name'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('surname')
                    ->label(__('admin.users.fields.surname'))
                    ->maxLength(255),
                TextInput::make('email')
                    ->label(__('admin.users.fields.email'))
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true),
                TextInput::make('phone')
                    ->label(__('admin.users.fields.phone'))
                    ->numeric()
                    ->unique(ignoreRecord: true),
                Select::make('roles')
                    ->label(__('admin.users.fields.roles'))
                    ->multiple()
                    ->relationship('roles', 'name')
                    ->preload(),
                DateTimePicker::make('email_verified_at')
                    ->label(__('admin.users.fields.email_verified_at')),
                TextInput::make('password')
                    ->label(__('admin.users.fields.password'))
                    ->password()
                    ->dehydrated(fn($state) => filled($state))
                    ->dehydrateStateUsing(fn($state) => filled($state) ? Hash::make($state) : null)
                    ->required(fn(string $context) => $context === 'create'),
                TextInput::make('ip')
                    ->label(__('admin.users.fields.ip'))
                    ->disabled()
                    ->dehydrated(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('admin.users.fields.name'))
                    ->searchable(),
                TextColumn::make('name')
                    ->label(__('admin.users.fields.surname'))
                    ->searchable(),
                TextColumn::make('email')
                    ->label(__('admin.users.fields.email'))
                    ->searchable(),
                TextColumn::make('phone')
                    ->label(__('admin.users.fields.phone'))
                    ->searchable(),
                TextColumn::make('roles.name')->badge()
                    ->label(__('admin.users.fields.roles')),
                TextColumn::make('ip')
                    ->label(__('admin.users.fields.ip'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('email_verified_at')
                    ->label(__('admin.users.fields.email_verified_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                SelectFilter::make('roles')
                    ->label(__('admin.users.fields.roles'))
                    ->relationship('roles', 'name'),
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
            'index' => ManageUsers::route('/'),
        ];
    }
}
