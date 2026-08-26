<?php

namespace App\Modules\ShopCart\Actions;

use App\Modules\Core\Dtos\ActionResult;
use App\Modules\ShopCart\Dtos\PromocodeData;
use App\Modules\ShopCart\Models\Cart;

readonly class ApplyPromocodeToCartAction
{
    public function __construct(
        private ValidatePromocodeAction $validatePromocode,
        private RecalculateCartTotalAction $recalculateCartTotal,
    ) {}

    /**
     * @return ActionResult<PromocodeData>
     */
    public function handle(Cart $cart, string $code): ActionResult
    {
        $subtotal = (float) $cart->items()->sum('total');

        $result = $this->validatePromocode->handle($code, $subtotal);

        if (! $result->success) {
            return $result;
        }

        // Only the link is written here; the money is recalculated in one place.
        $cart->withoutEvents(fn () => $cart->update([
            'promocode_id' => $result->data->promocode_id,
            'promocode_code' => $result->data->code,
        ]));

        $this->recalculateCartTotal->handle($cart);

        return $result;
    }
}
