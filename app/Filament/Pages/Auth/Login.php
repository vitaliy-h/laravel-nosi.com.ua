<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Components\TextInput;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Schemas\Schema;
use App\Modules\User\Models\User;
use Illuminate\Validation\ValidationException;

class Login extends BaseLogin
{
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('login')
                    ->label(__('admin.auth.login'))
                    ->required()
                    ->autocomplete('login')
                    ->autofocus()
                    ->default(request()->getHost() === 'abc-cms.com' ? 'admin' : null)
                    ->extraInputAttributes(['tabindex' => 1]),
                $this->getPasswordFormComponent()->default(request()->getHost() === 'abc-cms.com' ? 'password' : null),
                $this->getRememberFormComponent(),
            ]);
    }

    protected function getCredentialsFromFormData(array $data): array
    {
        $user = User::query()->where('email', $data['login'])->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'data.login' => __('filament-panels::auth/pages/login.messages.failed'),
            ]);
        }

        return [
            'email'    => $user->email,
            'password' => $data['password'],
        ];
    }

    protected function throwFailureValidationException(): never
    {
        throw ValidationException::withMessages([
            'data.login' => __('filament-panels::auth/pages/login.messages.failed'),
        ]);
    }
}
