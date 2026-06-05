<?php

namespace App\Modules\Shop\Observers;

use App\Modules\Shop\Actions\GenerateOrderNumberAction;
use App\Modules\Shop\Models\Order;

readonly class OrderObserver
{
    public function __construct(
        private GenerateOrderNumberAction $generateOrderNumber,
    )
    {
    }

    public function created(Order $order): void
    {
        $this->generateOrderNumber->handle($order);
    }

    public function updated(Order $order): void
    {
        $this->generateOrderNumber->handle($order);
    }
}
