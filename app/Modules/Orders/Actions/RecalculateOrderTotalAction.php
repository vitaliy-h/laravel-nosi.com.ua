<?php

namespace App\Modules\Orders\Actions;

use App\Modules\Orders\Models\Order;

class RecalculateOrderTotalAction
{
    public function handle(Order $order): void
    {
        $total = $order->items()->sum('total');

        $order->withoutEvents(fn() => $order->update(['total_amount' => $total]));
    }
}
