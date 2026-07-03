<?php

namespace App\Modules\Orders\Observers;

use App\Modules\Orders\Actions\RecalculateOrderTotalAction;
use App\Modules\Orders\Models\OrderProduct;

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
