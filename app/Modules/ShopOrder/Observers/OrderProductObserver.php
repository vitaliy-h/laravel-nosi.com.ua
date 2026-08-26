<?php

namespace App\Modules\ShopOrder\Observers;

use App\Modules\ShopOrder\Actions\RecalculateOrderTotalAction;
use App\Modules\ShopOrder\Models\OrderProduct;

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
