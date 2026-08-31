<?php

namespace App\Modules\Core\Support;

use App\Modules\Core\Enums\AuthLogSourceEnum;

/**
 * Source resolver for the auth log.
 *
 * Unlike LogSourceResolver it checks the HTTP request first: authentication
 * always happens inside a request, and under tests `runningInConsole()` is
 * true, which would otherwise collapse every source into one value.
 */
class AuthLogSourceResolver
{
    public static function resolve(): AuthLogSourceEnum
    {
        if (filament()->getCurrentPanel()) {
            return AuthLogSourceEnum::ADMIN_PANEL;
        }

        if (request()->is('api/*')) {
            return AuthLogSourceEnum::API;
        }

        return AuthLogSourceEnum::WEBSITE;
    }
}
