<?php

namespace App\Filament\Pages\Auth;

use App\Modules\Core\Enums\AuthLogActionEnum;
use App\Modules\Core\Enums\AuthLogSourceEnum;
use App\Modules\Core\Exceptions\AuthCooldownException;
use App\Modules\Core\Services\AuthCooldownService;
use App\Modules\Core\Services\AuthLogService;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Facades\Filament;
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

    /**
     * The admin panel signs in through this page instead of LoginAction,
     * so the auth journal and the cooldown are wired in here as well.
     */
    public function authenticate(): ?LoginResponse
    {
        $login = $this->submittedLogin();

        try {
            app(AuthCooldownService::class)->assertNotBlocked($login);
        } catch (AuthCooldownException $exception) {

            app(AuthLogService::class)->blocked(
                AuthLogActionEnum::PASSWORD,
                $login,
                source: AuthLogSourceEnum::ADMIN_PANEL,
            );

            throw $exception->asValidationException(
                'data.login',
                __('admin.auth_log.cooldown', ['seconds' => $exception->seconds]),
            );
        }

        $response = parent::authenticate();

        if ($response === null) {
            // rate limited by Filament or a multi-factor challenge is pending
            return $response;
        }

        app(AuthCooldownService::class)->clear($login);

        app(AuthLogService::class)->success(
            AuthLogActionEnum::PASSWORD,
            $login,
            Filament::auth()->user(),
            AuthLogSourceEnum::ADMIN_PANEL,
        );

        return $response;
    }

    protected function getCredentialsFromFormData(array $data): array
    {
        $user = User::query()->where('email', $data['login'])->first();

        if (! $user) {
            $this->throwFailureValidationException();
        }

        return [
            'email'    => $user->email,
            'password' => $data['password'],
        ];
    }

    protected function throwFailureValidationException(): never
    {
        $login = $this->submittedLogin();

        app(AuthCooldownService::class)->hit($login);

        app(AuthLogService::class)->failure(
            AuthLogActionEnum::PASSWORD,
            $login,
            User::query()->where('email', $login)->first(),
            AuthLogSourceEnum::ADMIN_PANEL,
        );

        throw ValidationException::withMessages([
            'data.login' => __('filament-panels::auth/pages/login.messages.failed'),
        ]);
    }

    private function submittedLogin(): string
    {
        return (string)($this->data['login'] ?? '');
    }
}
