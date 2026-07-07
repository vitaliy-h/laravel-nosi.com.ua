<?php

namespace App\Modules\Feedback\Dtos;

use Spatie\LaravelData\Data;

class CreateFeedbackData extends Data
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly string $message,
        public readonly string $ip,
    ) {}
}
