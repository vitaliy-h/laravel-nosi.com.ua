<?php

namespace App\Modules\ShopOrders\Dtos;

use Spatie\LaravelData\Data;

class CustomerData extends Data
{
    public function __construct(
        public readonly ?string $user_name = null,
        public readonly ?string $user_surname = null,
        public readonly ?string $user_email = null,
        public readonly ?string $user_phone = null,
        public readonly ?string $comment = null,
    ) {}
}
