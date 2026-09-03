<?php

namespace App\Modules\UserAuth\ViewModels;

use App\Modules\Core\Abstract\BaseViewModel;

/**
 * The auth screen. `mode` picks which form template is included, so it is not
 * taken at face value: anything outside MODES falls back to the login form and
 * an arbitrary string can never reach `@include`.
 */
class AuthPageViewModel extends BaseViewModel
{
    public const MODES = [
        'login',
        'register',
        'password_recovery',
        'password_reset',
    ];

    public function __construct(
        public readonly string $mode,
        public readonly string $form_view,
        public readonly bool $is_login,
        public readonly bool $is_register,
        public readonly ?string $token,
        public readonly ?string $email,
    ) {}

    public static function make(?string $mode, ?string $token, ?string $email): self
    {
        $mode = in_array($mode, self::MODES, true) ? $mode : 'login';

        return new self(
            mode: $mode,
            form_view: 'modules.user_auth.partials.forms.'.$mode,
            is_login: $mode === 'login',
            is_register: $mode === 'register',
            token: $token,
            email: $email,
        );
    }
}
