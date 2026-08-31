<?php

namespace App\Modules\Core\Enums;

use App\Modules\Core\Traits\Enums\HasEnumHelpers;

/**
 * Where a sign-in came from.
 *
 * Deliberately not LogSourceEnum: that one serves the change log and carries
 * `console` / `system`, which no authentication attempt can ever have.
 * The mobile app talks to the same endpoints as any other API client, so it
 * shares the `api` case.
 */
enum AuthLogSourceEnum: string
{
    use HasEnumHelpers;

    case WEBSITE = 'website';
    case ADMIN_PANEL = 'admin_panel';
    case API = 'api';

    public function label(): string
    {
        return __("admin.auth_log.sources.{$this->value}");
    }
}
