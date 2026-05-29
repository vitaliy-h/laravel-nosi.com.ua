<?php

namespace App\Modules\Auth\Dtos;

use Spatie\LaravelData\Data;

class PasswordResetData extends Data
{
    public function __construct(
        public readonly string $email,
        public readonly string $password,
        public readonly string $password_confirmation,
        public readonly string $token,
    ) {}
}
