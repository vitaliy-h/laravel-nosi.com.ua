<?php

namespace App\Modules\ShopOrders\Services;

use App\Modules\ShopOrders\Enums\PaymentStatusEnum;
use App\Modules\ShopOrders\Models\Order;

readonly class OrderPermissions
{
    public function __construct(private Order $order)
    {
    }

    public function canPay(): bool
    {
        return $this->order->payment_status === PaymentStatusEnum::UNPAID;
    }
}
