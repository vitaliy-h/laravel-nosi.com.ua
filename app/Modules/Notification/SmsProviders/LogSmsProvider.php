<?php

namespace App\Modules\Notification\SmsProviders;

use App\Modules\Notification\Contracts\SmsProviderInterface;

class LogSmsProvider implements SmsProviderInterface
{
    public function send(string $to, string $body): void
    {
        // No-op: SMS is logged to DB by SmsTemplateService before this call
    }
}
