<?php

namespace App\Modules\Notification\Traits;

use App\Modules\Notification\Enums\NotificationSourceEnum;

trait DetectsNotificationSource
{
    protected function detectSource(): NotificationSourceEnum
    {
        if (app()->runningInConsole()) {
            return NotificationSourceEnum::CONSOLE;
        }

        if (request()?->is('admin/*')) {
            return NotificationSourceEnum::ADMIN;
        }

        return NotificationSourceEnum::SYSTEM;
    }
}
