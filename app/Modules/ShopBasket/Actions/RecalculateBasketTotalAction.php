<?php

namespace App\Modules\ShopBasket\Actions;

use App\Modules\ShopBasket\Models\Basket;
use App\Modules\ShopBasket\Services\BasketService;

class RecalculateBasketTotalAction
{
    public function __construct(
        private BasketService $basketService,
    ) {}

    public function handle(Basket $basket): void
    {
        $total = $basket->items()->sum('total');

        $basket->withoutEvents(fn() => $basket->update(['total_amount' => $total]));

        $this->basketService->refreshCount($basket);
    }
}
