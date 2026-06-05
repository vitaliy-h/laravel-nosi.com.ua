<?php

namespace App\Modules\Shop\Observers;

use App\Modules\Shop\Actions\RecalculateOrderTotalAction;
use App\Modules\Shop\Models\OrderProduct;

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
