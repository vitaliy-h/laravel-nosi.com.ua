<?php

namespace App\Modules\Notification\Contracts;

interface SmsProviderInterface
{
    public function send(string $to, string $body): void;
}
