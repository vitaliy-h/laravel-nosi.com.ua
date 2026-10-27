<?php

namespace App\Modules\ShopCart\Actions;

use App\Modules\Shop\Models\Product;
use App\Modules\Shop\ViewModels\ProductViewModel;
use App\Modules\ShopCart\Models\Cart;
use App\Modules\ShopCart\Models\CartProduct;
use InvalidArgumentException;

/**
 * Puts `$quantity` sets of `$pack` pieces of a product into the cart — or sets
 * the count of a line already there. A line is the product plus the set size:
 * «3 × 5 шт.» and «2 × 10 шт.» of one product are two lines.
 */
class AddOrUpdateCartItemAction
{
    public function handle(Cart $cart, Product $product, int $quantity, int $pack = 1): CartProduct
    {
        $set = $product->pack($pack)
            ?? throw new InvalidArgumentException("Product {$product->id} is not sold in sets of {$pack}.");

        return CartProduct::updateOrCreate(
            [
                'cart_id'         => $cart->id,
                'shop_product_id' => $product->id,
                'pack_quantity'   => $pack,
            ],
            [
                'product_name'    => self::lineName($product, $pack),
                'product_article' => $product->article,
                'selling_price'   => $set['price'],
                'quantity'        => $quantity,
                'total'           => round($set['price'] * $quantity, 2),
            ],
        );
    }

    /**
     * Puts `$quantity` more sets into the cart: «У кошик» with 2 pressed five
     * times leaves 10, where `handle()` would leave 2.
     */
    public function add(Cart $cart, Product $product, int $quantity, int $pack = 1): CartProduct
    {
        $inCart = (int) $cart->items()
            ->where('shop_product_id', $product->id)
            ->where('pack_quantity', $pack)
            ->value('quantity');

        return $this->handle($cart, $product, $inCart + $quantity, $pack);
    }

    /**
     * The name snapshot carries the set («Шкарпетки — 5 шт.»), so the cart, the
     * order, the admin and the letters all tell the sets apart with no change
     * of theirs. A product sold by the piece only keeps its bare title.
     */
    public static function lineName(Product $product, int $pack): string
    {
        $sold = count(Product::normalizePacks($product->packs));

        if ($sold <= 1 && $pack === 1 && !$product->is_pack) {
            return (string) $product->title;
        }

        return $product->title . ' — ' . $pack . ' ' . ProductViewModel::unit((bool) $product->is_pack);
    }
}
