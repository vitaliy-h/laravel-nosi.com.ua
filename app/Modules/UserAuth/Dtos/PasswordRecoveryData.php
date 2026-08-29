<?php

namespace App\Modules\UserAuth\Dtos;

use Spatie\LaravelData\Data;

class PasswordRecoveryData extends Data
{
    public function __construct(
        public readonly string $email,
    )
    {
    }
}
