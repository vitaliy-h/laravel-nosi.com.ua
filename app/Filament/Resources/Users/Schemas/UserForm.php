<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('admin.users.fields.name'))
                    ->required(),
                TextInput::make('email')
                    ->label(__('admin.users.fields.email'))
                    ->email()
                    ->required(),
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
            ]);
    }
}
