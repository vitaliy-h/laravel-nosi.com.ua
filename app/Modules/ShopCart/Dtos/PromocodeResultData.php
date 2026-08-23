<?php

namespace App\Modules\ShopCart\Dtos;

use Spatie\LaravelData\Data;

class PromocodeResultData extends Data
{
    public function __construct(
        public readonly bool $success,
        public readonly ?string $message = null,
        public readonly ?int $promocode_id = null,
        public readonly ?string $code = null,
        public readonly float $discount = 0.0,
    ) {}

    public static function fail(string $message): self
    {
        return new self(success: false, message: $message);
    }

    public static function ok(int $promocodeId, string $code, float $discount, string $message): self
    {
        return new self(
            success: true,
            message: $message,
            promocode_id: $promocodeId,
            code: $code,
            discount: $discount,
        );
    }
}
