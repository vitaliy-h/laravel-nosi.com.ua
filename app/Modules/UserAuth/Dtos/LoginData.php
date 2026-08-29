<?php

namespace App\Modules\UserAuth\Dtos;

use Spatie\LaravelData\Data;

class LoginData extends Data
{
    public function __construct(
        public readonly string $email,
        public readonly string $password,
    )
    {
    }
}
