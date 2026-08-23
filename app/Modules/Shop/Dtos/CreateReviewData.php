<?php

namespace App\Modules\Shop\Dtos;

use Spatie\LaravelData\Data;

class CreateReviewData extends Data
{
    public function __construct(
        public readonly int $productId,
        public readonly int $rating,
        public readonly string $name,
        public readonly string $email,
        public readonly string $text,
        public readonly ?string $ip = null,
        public readonly ?int $userId = null,
    ) {}
}
