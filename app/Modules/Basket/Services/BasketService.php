<?php

namespace App\Modules\Basket\Services;

use App\Modules\Basket\Actions\FindBasketAction;
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
            $basket = $this->findBasket->handle(Auth::user());
            Session::put('basket_count', $basket?->items()->sum('quantity') ?? 0);
        }

        return Session::get('basket_count');
    }
}
