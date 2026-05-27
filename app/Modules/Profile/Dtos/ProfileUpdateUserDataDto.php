<?php

namespace App\Modules\Profile\Dtos;

use Spatie\LaravelData\Data;

class ProfileUpdateUserDataDto extends Data
{
    public function __construct(
        public string $name,
        public string $email,
    )
    {
    }
}
