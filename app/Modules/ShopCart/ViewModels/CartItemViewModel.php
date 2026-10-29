<?php

namespace App\Modules\ShopCart\ViewModels;

use App\Modules\Core\Abstract\BaseViewModel;
use App\Modules\Core\Support\Money;
use App\Modules\ShopCart\Models\CartProduct;

class CartItemViewModel extends BaseViewModel
{
    public function __construct(
        public readonly int $product_id,
        /** The set size of the line — sent back with every change of it. */
        public readonly int $pack_quantity,
        /** Tells two sets of one product apart in the page markup. */
        public readonly string $line_key,
        public readonly string $name,
        public readonly bool $has_image,
        public readonly string $image,
        public readonly int $quantity,
        public readonly string $selling_price_formatted,
        public readonly string $total_formatted,
    ) {}

    /**
     * `name` is the snapshot taken when the line was added, not the product's
     * current title — renaming a product must not rewrite existing carts.
     * The picture, which is not snapshotted, comes from the product itself, so
     * the caller has to eager-load it.
     */
    public static function fromCartProduct(CartProduct $item): self
    {
        $product = $item->relationLoaded('product') ? $item->product : null;
        $pack = (int) ($item->pack_quantity ?? 1);

        return new self(
            product_id: (int) $item->shop_product_id,
            pack_quantity: $pack,
            line_key: self::lineKey((int) $item->shop_product_id, $pack),
            name: (string) $item->product_name,
            has_image: !empty($product?->img),
            image: $product?->imageUrl('card') ?? asset('/assets/images/no_image.svg'),
            quantity: (int) $item->quantity,
            selling_price_formatted: Money::from($item->selling_price)->format(),
            total_formatted: Money::from($item->total)->format(),
        );
    }

    public static function lineKey(int $productId, int $pack): string
    {
        return $productId . '-' . $pack;
    }
}
