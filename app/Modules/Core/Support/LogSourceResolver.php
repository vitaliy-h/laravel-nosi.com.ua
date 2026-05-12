<?php

namespace App\Modules\Core\Support;

use App\Modules\Core\Enums\LogSourceEnum;

class LogSourceResolver
{
    public static function resolve(): LogSourceEnum
    {
        if (app()->runningInConsole()) {

            if (app()->runningUnitTests()) {
                return LogSourceEnum::SYSTEM;
            }

            return LogSourceEnum::CONSOLE;
        }

        if (filament()->getCurrentPanel()) {
            return LogSourceEnum::ADMIN_PANEL;
        }

        if (request()->is('api/*')) {
            return LogSourceEnum::API;
        }

        return LogSourceEnum::CLIENT;
    }
}
