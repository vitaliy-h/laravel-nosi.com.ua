<?php

namespace App\Modules\Orders\Observers;

use App\Modules\Orders\Actions\GenerateOrderNumberAction;
use App\Modules\Orders\Models\Order;

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
