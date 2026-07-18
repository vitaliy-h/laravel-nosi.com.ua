<?php

namespace App\Filament\Resources\Users;

use App\Filament\Actions\BaseEditAction;
use App\Filament\Resources\Users\Pages\ManageUsers;
use App\Modules\Auth\Actions\GenerateImpersonationTokenAction;
use App\Modules\Core\Models\User;
use App\Modules\Core\Support\ModuleGate;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    /**
     * Super Admin accounts must be completely hidden from every other admin user
     * (list, search, filters, relation managers all reuse this query).
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (!auth()->user()?->isSuperAdmin()) {
            $query->whereDoesntHave('roles', fn ($q) => $q->where('name', 'super_admin'));
        }

        return $query->addSelect([
            'role_priority' => DB::table('model_has_roles')
                ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                ->whereColumn('model_has_roles.model_id', 'users.id')
                ->where('model_has_roles.model_type', User::class)
                ->selectRaw("MIN(CASE roles.name WHEN 'super_admin' THEN 0 WHEN 'admin' THEN 1 ELSE 2 END)"),
        ]);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('surname')
                    ->maxLength(255),
                TextInput::make('email')
                    ->email()
                    ->unique(ignoreRecord: true),
                TextInput::make('phone')
                    ->numeric()
                    ->unique(ignoreRecord: true),
                Select::make('roles')
                    ->multiple()
                    ->relationship(
                        'roles',
                        'name',
                        modifyQueryUsing: fn ($query) => auth()->user()?->isSuperAdmin()
                            ? $query
                            : $query->where('name', '!=', 'super_admin'),
                    )
                    ->preload(),
                DateTimePicker::make('email_verified_at'),
                TextInput::make('password')
                    ->password()
                    ->dehydrated(fn($state) => filled($state))
                    ->dehydrateStateUsing(fn($state) => filled($state) ? Hash::make($state) : null)
                    ->required(fn(string $context) => $context === 'create'),
                TextInput::make('ip')
                    ->disabled()
                    ->dehydrated(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'DESC')
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('surname')
                    ->searchable(),
                TextColumn::make('email')
                    ->searchable(),
                TextColumn::make('phone')
                    ->searchable(),
                TextColumn::make('roles.name')
                    ->badge(),
                TextColumn::make('ip')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('email_verified_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            ->defaultSort('role_priority')
            ->filters([
                SelectFilter::make('roles')
                    ->relationship('roles', 'name'),
            ])
            ->recordActions([
                Action::make('impersonate')
                    ->icon('heroicon-o-identification')
                    ->iconButton()
                    ->tooltip(__('admin.actions.impersonate'))
                    ->url(fn(User $record): string => app(GenerateImpersonationTokenAction::class)->handle($record))
                    ->openUrlInNewTab()
                    // Impersonation is served by the Auth module's routes, so it's
                    // unavailable when that module is disabled.
                    ->visible(fn(): bool => ModuleGate::isEnabled('auth'))
                    ->hidden(fn(User $record): bool => $record->id === auth()->id()),
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
