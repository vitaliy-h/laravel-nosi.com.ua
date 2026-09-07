<?php

namespace App\Modules\Notification\Support;

use App\Modules\Notification\Enums\NotificationSourceEnum;

/**
 * Where a letter or an SMS was sent from, when the caller did not say.
 *
 * A named resolver rather than a trait mixed into the template services: reading
 * ambient state is a job of its own, and giving it one owner keeps it out of the
 * services themselves (`.claude/backend.md`, «Request-scoped state»). Same shape
 * as `Core\Support\LogSourceResolver`.
 */
class NotificationSourceResolver
{
    public static function resolve(): NotificationSourceEnum
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
