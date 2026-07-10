<?php

namespace App\Modules\ShopBasket\Actions;

use App\Modules\ShopBasket\Models\Basket;
use App\Modules\Shop\Models\Product;

class RemoveBasketItemAction
{
    public function __construct(
        private RecalculateBasketTotalAction $recalculate,
    ) {}

    public function handle(Basket $basket, Product $product): void
    {
        $basket->items()->where('shop_product_id', $product->id)->delete();

        $this->recalculate->handle($basket);
    }
}
