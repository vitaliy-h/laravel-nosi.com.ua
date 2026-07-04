<?php

namespace App\Modules\Basket\Actions;

use App\Modules\Basket\Models\Basket;
use App\Modules\Basket\Models\BasketProduct;
use App\Modules\Shop\Models\Product;

class AddOrUpdateBasketItemAction
{
    public function handle(Basket $basket, Product $product, int $quantity): BasketProduct
    {
        return BasketProduct::updateOrCreate(
            [
                'basket_id' => $basket->id,
                'shop_product_id' => $product->id,
            ],
            [
                'product_name' => $product->title,
                'product_article' => $product->article,
                'selling_price' => $product->price,
                'quantity' => $quantity,
                'total' => round($product->price * $quantity, 2),
            ],
        );
    }
}
