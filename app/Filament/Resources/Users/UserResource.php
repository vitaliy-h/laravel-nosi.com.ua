<?php

namespace App\Filament\Resources\Users;

use App\Filament\Actions\BaseEditAction;
use App\Filament\Resources\Users\Pages\ManageUsers;
use App\Filament\Tables\Columns\InlineEditColumn;
use App\Modules\Core\Support\ModuleGate;
use App\Modules\User\Models\User;
use App\Modules\UserAuth\Actions\GenerateImpersonationTokenAction;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    public static function getModelLabel(): string
    {
        return __('admin.models.user');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.tables.users');
    }

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
            ->columns(12)
            ->components([
                TextInput::make('name')
                    ->columnSpan(4)
                    ->required()
                    ->maxLength(255),
                TextInput::make('surname')
                    ->columnSpan(4)
                    ->maxLength(255),
                Select::make('roles')
                    ->columnSpan(4)
                    ->multiple()
                    ->relationship(
                        'roles',
                        'name',
                        modifyQueryUsing: fn ($query) => auth()->user()?->isSuperAdmin()
                            ? $query
                            : $query->where('name', '!=', 'super_admin'),
                    )
                    ->preload(),

                TextInput::make('phone')
                    ->columnSpan(4)
                    ->numeric()
                    ->unique(ignoreRecord: true),
                TextInput::make('email')
                    ->columnSpan(4)
                    ->email()
                    ->unique(ignoreRecord: true),
                DateTimePicker::make('email_verified_at')
                    ->columnSpan(4),

                TextInput::make('password')
                    ->columnSpan(4)
                    ->password()
                    ->dehydrated(fn ($state) => filled($state))
                    ->dehydrateStateUsing(fn ($state) => filled($state) ? Hash::make($state) : null)
                    ->required(fn (string $context) => $context === 'create'),
                TextInput::make('ip')
                    ->columnSpan(4)
                    ->disabled()
                    ->dehydrated(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'DESC')
            ->columns([
                InlineEditColumn::make('name')
                    ->rules(['required', 'max:255'])
                    ->searchable(),
                TextColumn::make('surname')
                    ->searchable(),
                TextColumn::make('email')
                    ->searchable(),
                TextColumn::make('phone')
                    ->searchable(),
                // Same role list as the form: super_admin is offered to super admins only.
                InlineEditColumn::make('roles.name')
                    ->editAttribute('roles')
                    ->multiple()
                    ->options(fn (): array => Role::query()
                        ->when(!auth()->user()?->isSuperAdmin(), fn ($query) => $query->where('name', '!=', 'super_admin'))
                        ->pluck('name', 'id')
                        ->all())
                    // Only the offered roles: an admin must not post super_admin's id.
                    ->rules(fn (InlineEditColumn $column): array => [
                        'array',
                        function (string $attribute, mixed $value, \Closure $fail) use ($column): void {
                            if (array_diff((array) $value, array_keys($column->getOptions()))) {
                                $fail(__('validation.in', ['attribute' => 'roles']));
                            }
                        },
                    ])
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
                    ->url(fn (User $record): string => app(GenerateImpersonationTokenAction::class)->handle($record))
                    ->openUrlInNewTab()
                    // Impersonation is served by the Auth module's routes, so it's
                    // unavailable when that module is disabled.
                    ->visible(fn (): bool => ModuleGate::isEnabled('user_auth'))
                    ->hidden(fn (User $record): bool => $record->id === auth()->id()),
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
            'index' => ManageUsers::route('/'),
        ];
    }
}
