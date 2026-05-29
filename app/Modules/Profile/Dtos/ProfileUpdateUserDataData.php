<?php

namespace App\Modules\Profile\Dtos;

use Spatie\LaravelData\Data;

class ProfileUpdateUserDataData extends Data
{
    public function __construct(
        public string $name,
        public string $email,
    )
    {
    }
}
