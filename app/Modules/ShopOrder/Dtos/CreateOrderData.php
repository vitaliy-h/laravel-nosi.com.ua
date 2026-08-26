<?php

namespace App\Modules\ShopOrder\Dtos;

use App\Modules\ShopOrder\Enums\PaymentStatusEnum;
use App\Modules\ShopOrder\Enums\PaymentTypeEnum;
use Spatie\LaravelData\Data;

class CreateOrderData extends Data
{
    public function __construct(
        public readonly ?int $user_id = null,
        public readonly PaymentStatusEnum $payment_status = PaymentStatusEnum::UNPAID,
        public readonly PaymentTypeEnum $payment_type = PaymentTypeEnum::NONE,
        /** @var OrderItemData[] */
        public readonly array $items = [],
        public readonly ?string $user_name = null,
        public readonly ?string $user_surname = null,
        public readonly ?string $user_email = null,
        public readonly ?string $user_phone = null,
        public readonly ?string $comment = null,
        public readonly ?int $promocode_id = null,
        public readonly ?string $promocode_code = null,
        public readonly float $discount_amount = 0.0,
    ) {}
}
