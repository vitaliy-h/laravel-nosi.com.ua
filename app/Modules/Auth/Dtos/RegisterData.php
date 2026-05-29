<?php

namespace App\Modules\Auth\Dtos;

use Spatie\LaravelData\Data;

class RegisterData extends Data
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly string $password,
    ) {}
}
