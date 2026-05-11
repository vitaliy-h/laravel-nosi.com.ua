<?php

namespace App\Modules\Core\Support;

use App\Modules\Core\Enums\ActivityLogSourceEnum;

class ActivityLogSourceResolver
{
    public static function resolve(): ActivityLogSourceEnum
    {
        if (app()->runningInConsole()) {

            if (app()->runningUnitTests()) {
                return ActivityLogSourceEnum::SYSTEM;
            }

            return ActivityLogSourceEnum::CONSOLE;
        }

        if (filament()->getCurrentPanel()) {
            return ActivityLogSourceEnum::ADMIN_PANEL;
        }

        if (request()->is('api/*')) {
            return ActivityLogSourceEnum::API;
        }

        return ActivityLogSourceEnum::CLIENT;
    }
}
