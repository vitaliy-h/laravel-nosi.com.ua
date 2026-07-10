<?php

namespace App\Modules\ShopOrders\Observers;

use App\Modules\ShopOrders\Actions\RecalculateOrderTotalAction;
use App\Modules\ShopOrders\Models\OrderProduct;

readonly class OrderProductObserver
{
    public function __construct(
        private RecalculateOrderTotalAction $recalculate,
    ) {}

    public function created(OrderProduct $orderProduct): void
    {
        $this->recalculate->handle($orderProduct->order);
    }

    public function updated(OrderProduct $orderProduct): void
    {
        $this->recalculate->handle($orderProduct->order);
    }

    public function deleted(OrderProduct $orderProduct): void
    {
        //
    }
}
