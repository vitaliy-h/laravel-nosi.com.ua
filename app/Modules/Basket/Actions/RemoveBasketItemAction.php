<?php

namespace App\Modules\Basket\Actions;

use App\Modules\Basket\Models\Basket;
use App\Modules\Shop\Models\Product;
use Illuminate\Support\Facades\Session;

class RemoveBasketItemAction
{
    public function __construct(
        private RecalculateBasketTotalAction $recalculate,
    ) {}

    public function handle(Basket $basket, Product $product): void
    {
        $basket->items()->where('shop_product_id', $product->id)->delete();

        $this->recalculate->handle($basket);
        Session::put('basket_count', $basket->items()->sum('quantity'));
    }
}
