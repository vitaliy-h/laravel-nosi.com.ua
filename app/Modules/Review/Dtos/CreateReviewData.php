<?php

namespace App\Modules\Review\Dtos;

use Spatie\LaravelData\Data;

class CreateReviewData extends Data
{
    public function __construct(
        public readonly int $rating,
        public readonly string $name,
        public readonly string $email,
        public readonly string $text,
        public readonly ?string $city = null,
        public readonly ?string $ip = null,
        public readonly ?int $userId = null,
    ) {}
}
