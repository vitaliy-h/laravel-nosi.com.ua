<?php

namespace App\Modules\ShopBasket\Observers;

use App\Modules\ShopBasket\Actions\RecalculateBasketTotalAction;
use App\Modules\ShopBasket\Models\BasketProduct;

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
