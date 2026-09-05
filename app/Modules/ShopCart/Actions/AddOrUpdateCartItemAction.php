<?php

namespace App\Modules\ShopCart\Actions;

use App\Modules\Shop\Models\Product;
use App\Modules\ShopCart\Models\Cart;
use App\Modules\ShopCart\Models\CartProduct;

class AddOrUpdateCartItemAction
{
    public function handle(Cart $cart, Product $product, int $quantity): CartProduct
    {
        return CartProduct::updateOrCreate(
            [
                'cart_id'         => $cart->id,
                'shop_product_id' => $product->id,
            ],
            [
                'product_name'    => $product->title,
                'product_article' => $product->article,
                'selling_price'   => $product->price,
                'quantity'        => $quantity,
                'total'           => round($product->price * $quantity, 2),
            ],
        );
    }
}
