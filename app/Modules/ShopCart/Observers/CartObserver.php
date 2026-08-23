<?php

namespace App\Modules\ShopCart\Observers;

use App\Modules\ShopCart\Actions\RecalculateCartTotalAction;
use App\Modules\ShopCart\Models\Cart;

readonly class CartObserver
{
    public function __construct(
        private RecalculateCartTotalAction $recalculate,
    ) {}

    public function updated(Cart $cart): void
    {
        // Admin changed the promocode in Filament — the total must follow.
        // No recursion: the recalculation writes through withoutEvents().
        if ($cart->wasChanged(['promocode_id', 'discount_amount'])) {
            $this->recalculate->handle($cart);
        }
    }
}
