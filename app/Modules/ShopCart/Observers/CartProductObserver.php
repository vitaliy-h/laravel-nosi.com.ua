<?php

namespace App\Modules\ShopCart\Observers;

use App\Modules\ShopCart\Actions\RecalculateCartTotalAction;
use App\Modules\ShopCart\Models\CartProduct;

readonly class CartProductObserver
{
    public function __construct(
        private RecalculateCartTotalAction $recalculate,
    ) {}

    public function created(CartProduct $cartProduct): void
    {
        $this->recalculate->handle($cartProduct->cart);
    }

    public function updated(CartProduct $cartProduct): void
    {
        $this->recalculate->handle($cartProduct->cart);
    }
}
