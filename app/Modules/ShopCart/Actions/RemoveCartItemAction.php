<?php

namespace App\Modules\ShopCart\Actions;

use App\Modules\Shop\Models\Product;
use App\Modules\ShopCart\Models\Cart;

class RemoveCartItemAction
{
    public function __construct(
        private RecalculateCartTotalAction $recalculate,
    ) {}

    /**
     * Drops the line of one set size of the product; the other sets stay.
     */
    public function handle(Cart $cart, Product $product, int $pack = 1): void
    {
        $cart->items()
            ->where('shop_product_id', $product->id)
            ->where('pack_quantity', $pack)
            ->delete();

        $this->recalculate->handle($cart);
    }
}
