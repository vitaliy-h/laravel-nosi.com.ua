<?php

namespace App\Modules\ShopOrder\Actions;

use App\Modules\Core\Support\Money;
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
        $subtotal = Money::from($order->items()->sum('total'));
        $discount = Money::from($order->discount_amount)
            ->atLeast(Money::zero())
            ->atMost($subtotal);

        $order->withoutEvents(fn () => $order->update([
            'subtotal_amount' => $subtotal->value(),
            'discount_amount' => $discount->value(),
            'total_amount'    => $subtotal->minus($discount)->atLeast(Money::zero())->value(),
        ]));
    }
}
