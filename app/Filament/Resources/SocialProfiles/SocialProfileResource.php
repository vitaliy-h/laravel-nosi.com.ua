<?php

namespace App\Filament\Resources\SocialProfiles;

use App\Filament\Resources\SocialProfiles\Pages\ManageSocialProfiles;
use App\Modules\UserAuth\Models\SocialProfile;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Table;

class SocialProfileResource extends Resource
{
    protected static ?string $model = SocialProfile::class;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'DESC')
            ->columns([
                ImageColumn::make('avatar')
                    ->circular(),
                TextColumn::make('user.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.email')
                    ->searchable(),
                TextColumn::make('provider')
                    ->badge()
                    ->sortable(),
                TextColumn::make('identifier')
                    ->searchable(),
                TextColumn::make('provider_id')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('token_expires_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('provider')
                    ->options(
                        collect(config('social.providers'))->mapWithKeys(fn ($p) => [$p => ucfirst($p)])->all()
                    ),
            ])
            ->recordActions([
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
            'index' => ManageSocialProfiles::route('/'),
        ];
    }
}
