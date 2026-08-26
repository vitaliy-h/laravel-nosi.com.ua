<?php

namespace App\Modules\Subscriber\Dtos;

use App\Modules\Subscriber\Models\Subscriber;
use Spatie\LaravelData\Data;

/**
 * Payload of a successful subscribe. A repeated email is not a failure, so the
 * envelope stays successful and the fact is carried here.
 */
class SubscriberData extends Data
{
    public function __construct(
        public readonly Subscriber $subscriber,
        public readonly bool $alreadyExists,
    ) {}
}
