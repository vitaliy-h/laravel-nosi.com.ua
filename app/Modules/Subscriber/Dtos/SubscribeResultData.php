<?php

namespace App\Modules\Subscriber\Dtos;

use App\Modules\Subscriber\Models\Subscriber;
use Spatie\LaravelData\Data;

class SubscribeResultData extends Data
{
    public function __construct(
        public readonly Subscriber $subscriber,
        public readonly bool $alreadyExists,
    ) {}
}
