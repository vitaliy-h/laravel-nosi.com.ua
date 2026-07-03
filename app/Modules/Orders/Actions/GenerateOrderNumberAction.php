<?php

namespace App\Modules\Orders\Actions;

use App\Modules\Orders\Models\Order;

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
