<?php

namespace App\Modules\Notification\Dtos;

use App\Modules\Notification\Enums\NotificationSourceEnum;
use App\Modules\Notification\Enums\SmsEnum;
use Spatie\LaravelData\Data;

class SendSmsData extends Data
{
    public function __construct(
        public SmsEnum $key,
        public string $to,
        public array $replace = [],
        public ?NotificationSourceEnum $source = null,
    ) {}
}
