<?php

namespace App\Modules\Basket\Observers;

use App\Modules\Basket\Actions\RecalculateBasketTotalAction;
use App\Modules\Basket\Models\BasketProduct;

readonly class BasketProductObserver
{
    public function __construct(
        private RecalculateBasketTotalAction $recalculate,
    ) {}

    public function created(BasketProduct $basketProduct): void
    {
        $this->recalculate->handle($basketProduct->basket);
    }

    public function updated(BasketProduct $basketProduct): void
    {
        $this->recalculate->handle($basketProduct->basket);
    }
}
