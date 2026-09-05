<?php

namespace App\Modules\ShopOrder\Services;

use App\Modules\ShopOrder\Enums\PaymentStatusEnum;
use App\Modules\ShopOrder\Models\Order;

readonly class OrderPermissions
{
    public function __construct(private Order $order) {}

    public function canPay(): bool
    {
        return $this->order->payment_status === PaymentStatusEnum::UNPAID;
    }
}
