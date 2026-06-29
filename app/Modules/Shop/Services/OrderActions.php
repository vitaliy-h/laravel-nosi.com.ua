<?php

namespace App\Modules\Shop\Services;

use App\Modules\Shop\Enums\PaymentStatusEnum;
use App\Modules\Shop\Models\Order;

readonly class OrderActions
{
    public function __construct(private Order $order)
    {
    }

    public function canPay(): bool
    {
        return $this->order->payment_status === PaymentStatusEnum::UNPAID;
    }
}
