<?php

namespace App\Modules\Auth\Dtos;

use Spatie\LaravelData\Data;

class RegisterData extends Data
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $email = null,
        public readonly ?string $password = null,
        public readonly mixed $emailVerifiedAt = null,
    ) {}
}
