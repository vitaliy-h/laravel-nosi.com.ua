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

            $argv = request()->server('argv', []);

            if (collect($argv)->contains(fn($arg) => str_contains($arg, 'db:seed'))) {
                return ActivityLogSourceEnum::SEEDER;
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
