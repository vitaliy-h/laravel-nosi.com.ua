<?php

namespace App\Modules\ShopOrders\Observers;

use App\Modules\ShopOrders\Actions\GenerateOrderNumberAction;
use App\Modules\ShopOrders\Models\Order;

readonly class OrderObserver
{
    public function __construct(
        private GenerateOrderNumberAction $generateOrderNumber,
    ) {}

    public function created(Order $order): void
    {
        $this->generateOrderNumber->handle($order);
    }

    public function updated(Order $order): void
    {
        $this->generateOrderNumber->handle($order);
    }
}
