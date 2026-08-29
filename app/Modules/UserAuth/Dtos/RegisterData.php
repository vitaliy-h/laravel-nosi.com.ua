<?php

namespace App\Modules\UserAuth\Dtos;

use Spatie\LaravelData\Data;

class RegisterData extends Data
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $surname = null,
        public readonly ?string $phone = null,
        public readonly ?string $email = null,
        public readonly ?string $password = null,
        public readonly mixed $emailVerifiedAt = null,
    ) {}
}
