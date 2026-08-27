<?php

namespace App\Modules\ShopCart\Observers;

use App\Modules\ShopCart\Actions\RecalculateCartTotalAction;
use App\Modules\ShopCart\Models\Cart;
use App\Modules\ShopCart\Models\CartProduct;

readonly class CartProductObserver
{
    public function __construct(
        private RecalculateCartTotalAction $recalculate,
    ) {}

    public function created(CartProduct $cartProduct): void
    {
        $this->refreshCartOf($cartProduct);
    }

    public function updated(CartProduct $cartProduct): void
    {
        $this->refreshCartOf($cartProduct);
    }

    /**
     * Covers the Filament repeater: RemoveCartItemAction recalculates by hand on
     * the storefront path, the admin one has no such call site.
     */
    public function deleted(CartProduct $cartProduct): void
    {
        $this->refreshCartOf($cartProduct);
    }

    /**
     * Resolved by id rather than through `$cartProduct->cart`: the relation may
     * be a cached instance loaded before the change, and the recalculation reads
     * the promocode columns off it. Null when the parent cart is itself gone.
     */
    private function refreshCartOf(CartProduct $cartProduct): void
    {
        $cart = Cart::find($cartProduct->cart_id);

        if ($cart) {
            $this->recalculate->handle($cart);
        }
    }
}
