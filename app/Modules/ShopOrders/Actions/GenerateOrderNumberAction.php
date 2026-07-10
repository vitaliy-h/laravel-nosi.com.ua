<?php

namespace App\Modules\ShopOrders\Actions;

use App\Modules\ShopOrders\Models\Order;

class GenerateOrderNumberAction
{
    public function handle(Order $order): void
    {
        if ($order->order_number) {
            return;
        }

        $order->withoutEvents(fn () => $order->forceFill(['order_number' => $order->id])->save());
    }
}
