<?php

namespace App\Modules\Subscriber\Dtos;

use Spatie\LaravelData\Data;

class SubscribeData extends Data
{
    public function __construct(
        public readonly string $email,
        public readonly ?string $ip = null,
    ) {}
}
