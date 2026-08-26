<?php

namespace App\Modules\ShopCart\Dtos;

use Spatie\LaravelData\Data;

/**
 * Payload of a successful promocode validation. Every field is non-nullable —
 * it only exists on the success branch; the envelope is App\Modules\Core\Dtos\ActionResult.
 */
class PromocodeData extends Data
{
    public function __construct(
        public readonly int $promocode_id,
        public readonly string $code,
        public readonly float $discount,
    ) {}
}
