<?php

namespace App\Modules\Basket\Services;

use App\Modules\Basket\Actions\FindBasketAction;
use App\Modules\Basket\Models\Basket;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class BasketService
{
    public function __construct(
        private FindBasketAction $findBasket,
    ) {}

    public function getCount(): int
    {
        if (!Session::has('basket_count')) {
            $this->refreshCount($this->findBasket->handle(Auth::user()));
        }

        return Session::get('basket_count');
    }

    /**
     * Recalculate the basket item count and store it in the session.
     */
    public function refreshCount(?Basket $basket): int
    {
        $count = $basket?->items()->sum('quantity') ?? 0;

        Session::put('basket_count', $count);

        return $count;
    }
}
