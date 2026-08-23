<?php

namespace App\Modules\Subscribers\Dtos;

use App\Modules\Subscribers\Models\Subscriber;
use Spatie\LaravelData\Data;

class SubscribeResultData extends Data
{
    public function __construct(
        public readonly Subscriber $subscriber,
        public readonly bool $alreadyExists,
    ) {}
}
