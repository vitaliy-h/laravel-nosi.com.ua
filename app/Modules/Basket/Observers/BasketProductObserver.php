<?php

namespace App\Modules\Basket\Observers;

use App\Modules\Basket\Actions\RecalculateBasketTotalAction;
use App\Modules\Basket\Models\BasketProduct;
use Illuminate\Support\Facades\Session;

readonly class BasketProductObserver
{
    public function __construct(
        private RecalculateBasketTotalAction $recalculate,
    ) {}

    public function created(BasketProduct $basketProduct): void
    {
        $this->recalculate->handle($basketProduct->basket);
        Session::put('basket_count', $basketProduct->basket->items()->sum('quantity'));
    }

    public function updated(BasketProduct $basketProduct): void
    {
        $this->recalculate->handle($basketProduct->basket);
        Session::put('basket_count', $basketProduct->basket->items()->sum('quantity'));
    }
}
