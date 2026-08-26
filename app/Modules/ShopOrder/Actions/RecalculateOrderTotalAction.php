<?php

namespace App\Modules\ShopOrder\Actions;

use App\Modules\ShopOrder\Models\Order;

class RecalculateOrderTotalAction
{
    /**
     * Unlike the cart, an order's discount is frozen: discount_amount is the
     * source of truth (an admin may set it by hand), the promocode is not
     * re-validated.
     */
    public function handle(Order $order): void
    {
        $subtotal = (float) $order->items()->sum('total');
        $discount = max(0, min((float) $order->discount_amount, $subtotal));

        $order->withoutEvents(fn () => $order->update([
            'subtotal_amount' => $subtotal,
            'discount_amount' => $discount,
            'total_amount' => max(0, round($subtotal - $discount, 2)),
        ]));
    }
}
