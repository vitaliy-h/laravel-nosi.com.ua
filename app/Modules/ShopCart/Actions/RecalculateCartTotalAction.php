<?php

namespace App\Modules\ShopCart\Actions;

use App\Modules\ShopCart\Models\Cart;
use App\Modules\ShopCart\Models\Promocode;

/**
 * Recomputes a cart's subtotal, discount and total.
 *
 * Runs from CartProductObserver, so it fires for any cart the process touches —
 * an admin editing a customer's cart in Filament, the abandoned-cart command.
 * That is why it does not refresh the session-cached badge count: the count
 * belongs to whoever is browsing, and CartContext owns it.
 */
class RecalculateCartTotalAction
{
    public function __construct(
        private ValidatePromocodeAction $validatePromocode,
    ) {}

    public function handle(Cart $cart): void
    {
        $subtotal = (float) $cart->items()->sum('total');

        $promocodeId = $cart->promocode_id;
        // An admin picking a promocode in Filament only sets the id, so the
        // denormalised code is filled in from the relation here.
        $promocodeCode = $cart->promocode_code
            ?: ($promocodeId !== null ? Promocode::find($promocodeId)?->code : null);
        $discount = 0.0;

        if ($promocodeId !== null && $promocodeCode !== null) {
            // Re-validate on every recalculation: items may have dropped below
            // min_order_amount, or the code may have expired since it was applied.
            $result = $this->validatePromocode->handle($promocodeCode, $subtotal);

            if ($result->success) {
                $discount = $result->data->discount;
            } else {
                $promocodeId = null;
                $promocodeCode = null;
            }
        } else {
            $promocodeId = null;
            $promocodeCode = null;
        }

        $cart->withoutEvents(fn () => $cart->update([
            'subtotal_amount' => $subtotal,
            'promocode_id'    => $promocodeId,
            'promocode_code'  => $promocodeCode,
            'discount_amount' => $discount,
            'total_amount'    => max(0, round($subtotal - $discount, 2)),
        ]));
    }
}
