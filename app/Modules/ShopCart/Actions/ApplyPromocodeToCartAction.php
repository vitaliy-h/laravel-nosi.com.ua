<?php

namespace App\Modules\ShopCart\Actions;

use App\Modules\ShopCart\Dtos\PromocodeResultData;
use App\Modules\ShopCart\Models\Cart;

readonly class ApplyPromocodeToCartAction
{
    public function __construct(
        private ValidatePromocodeAction $validatePromocode,
        private RecalculateCartTotalAction $recalculateCartTotal,
    ) {}

    public function handle(Cart $cart, string $code): PromocodeResultData
    {
        $subtotal = (float) $cart->items()->sum('total');

        $result = $this->validatePromocode->handle($code, $subtotal);

        if (! $result->success) {
            return $result;
        }

        // Only the link is written here; the money is recalculated in one place.
        $cart->withoutEvents(fn () => $cart->update([
            'promocode_id' => $result->promocode_id,
            'promocode_code' => $result->code,
        ]));

        $this->recalculateCartTotal->handle($cart);

        return $result;
    }
}
