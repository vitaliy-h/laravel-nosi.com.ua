<?php

namespace App\Modules\Shop\Dtos;

use App\Modules\Shop\Enums\PaymentStatusEnum;
use App\Modules\Shop\Enums\PaymentTypeEnum;
use Spatie\LaravelData\Data;

class CreateOrderData extends Data
{
    public function __construct(
        public readonly int $user_id,
        public readonly PaymentStatusEnum $payment_status = PaymentStatusEnum::UNPAID,
        public readonly PaymentTypeEnum $payment_type = PaymentTypeEnum::NONE,
        /** @var OrderItemData[] */
        public readonly array $items = [],
    ) {}
}
