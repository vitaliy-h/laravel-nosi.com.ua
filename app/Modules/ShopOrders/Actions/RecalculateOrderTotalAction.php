<?php

namespace App\Modules\ShopOrders\Actions;

use App\Modules\ShopOrders\Models\Order;

class RecalculateOrderTotalAction
{
    public function handle(Order $order): void
    {
        $total = $order->items()->sum('total');

        $order->withoutEvents(fn() => $order->update(['total_amount' => $total]));
    }
}
