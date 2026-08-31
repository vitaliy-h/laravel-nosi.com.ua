<?php

namespace App\Modules\Core\Enums;

use App\Modules\Core\Traits\Enums\HasEnumHelpers;

enum AuthLogActionEnum: string
{
    use HasEnumHelpers;

    case PASSWORD = 'login_password';
    case SOCIAL = 'social';
    case PASSWORD_RESET_REQUEST = 'password_reset_request';
    case PASSWORD_CHANGE = 'password_change';

    public function label(): string
    {
        return __("admin.auth_log.actions.{$this->value}");
    }
}
