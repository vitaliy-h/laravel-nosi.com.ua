<?php

namespace App\Modules\ShopCart\Actions;

use App\Modules\ShopCart\Models\Cart;

readonly class RemovePromocodeFromCartAction
{
    public function __construct(
        private RecalculateCartTotalAction $recalculateCartTotal,
    ) {}

    public function handle(Cart $cart): void
    {
        $cart->withoutEvents(fn () => $cart->update([
            'promocode_id'    => null,
            'promocode_code'  => null,
            'discount_amount' => 0,
        ]));

        $this->recalculateCartTotal->handle($cart);
    }
}
