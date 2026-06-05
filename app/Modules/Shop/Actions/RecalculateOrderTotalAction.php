<?php

namespace App\Modules\Shop\Actions;

use App\Modules\Shop\Models\Order;

class RecalculateOrderTotalAction
{
    public function handle(Order $order): void
    {
        $total = $order->items()->sum('total');

        $order->withoutEvents(fn() => $order->update(['total_amount' => $total]));
    }
}
