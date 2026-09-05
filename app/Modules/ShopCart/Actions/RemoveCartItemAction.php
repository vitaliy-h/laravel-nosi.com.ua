<?php

namespace App\Modules\ShopCart\Actions;

use App\Modules\Shop\Models\Product;
use App\Modules\ShopCart\Models\Cart;

class RemoveCartItemAction
{
    public function __construct(
        private RecalculateCartTotalAction $recalculate,
    ) {}

    public function handle(Cart $cart, Product $product): void
    {
        $cart->items()->where('shop_product_id', $product->id)->delete();

        $this->recalculate->handle($cart);
    }
}
