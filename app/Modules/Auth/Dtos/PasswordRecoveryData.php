<?php

namespace App\Modules\Auth\Dtos;

use Spatie\LaravelData\Data;

class PasswordRecoveryData extends Data
{
    public function __construct(
        public readonly string $email,
    )
    {
    }
}
