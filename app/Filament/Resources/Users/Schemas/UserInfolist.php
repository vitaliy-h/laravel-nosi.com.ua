<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')
                    ->label(__('admin.users.fields.name')),
                TextEntry::make('email')
                    ->label(__('admin.users.fields.email')),
                TextEntry::make('roles.name')
                    ->label(__('admin.users.fields.roles'))
                    ->badge()
                    ->formatStateUsing(fn($state) => $state ?? '—'),
                TextEntry::make('email_verified_at')
                    ->label(__('admin.users.fields.email_verified_at'))
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->label(__('admin.core.fields.created_at'))
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->label(__('admin.core.fields.updated_at'))
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
