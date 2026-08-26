<?php

namespace App\Modules\Profile\Dtos;

use App\Modules\ShopOrder\Enums\PaymentStatusEnum;
use Spatie\LaravelData\Data;

class OrderFilterData extends Data
{
    public function __construct(
        public readonly ?string $search,
        public readonly ?PaymentStatusEnum $status,
    ) {
    }
}
