<?php

namespace App\Modules\Orders\Services;

use App\Modules\Orders\Enums\PaymentStatusEnum;
use App\Modules\Orders\Models\Order;

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
