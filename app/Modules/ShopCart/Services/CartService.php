<?php

namespace App\Modules\ShopCart\Services;

use App\Modules\Core\Support\ModuleGate;
use App\Modules\ShopCart\Models\Cart;
use App\Modules\ShopCart\Queries\FindCartQuery;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CartService
{
    public function __construct(
        private FindCartQuery $findCart,
    ) {}

    public function getCount(): int
    {
        if (!ModuleGate::isEnabled('shop_cart')) {
            return 0;
        }

        if (!Session::has('cart_count')) {
            $this->refreshCount($this->findCart->handle(Auth::user()));
        }

        return Session::get('cart_count');
    }

    /**
     * Recalculate the cart item count and store it in the session.
     */
    public function refreshCount(?Cart $cart): int
    {
        $count = $cart?->items()->sum('quantity') ?? 0;

        Session::put('cart_count', $count);

        return $count;
    }
}
