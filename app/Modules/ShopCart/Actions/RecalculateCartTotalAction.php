<?php

namespace App\Modules\ShopCart\Actions;

use App\Modules\ShopCart\Models\Cart;
use App\Modules\ShopCart\Services\CartService;

class RecalculateCartTotalAction
{
    public function __construct(
        private CartService $cartService,
    ) {}

    public function handle(Cart $cart): void
    {
        $total = $cart->items()->sum('total');

        $cart->withoutEvents(fn() => $cart->update(['total_amount' => $total]));

        $this->cartService->refreshCount($cart);
    }
}
