<?php

namespace App\Modules\ShopOrder\ViewModels;

use App\Modules\Core\Abstract\BaseViewModel;
use App\Modules\ShopOrder\Models\OrderProduct;

class OrderItemViewModel extends BaseViewModel
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $article,
        public readonly int $quantity,
        public readonly string $total_formatted,
        public readonly bool $has_image,
        public readonly ?string $image,
        public readonly ?string $product_url,
    ) {}

    /**
     * Name and article are the snapshot taken when the order was placed. The
     * picture and the link come from the product, which may be gone or may have
     * lost its category since — both then simply resolve to null.
     */
    public static function fromOrderProduct(OrderProduct $item): self
    {
        $product = $item->relationLoaded('product') ? $item->product : null;
        $category = $product?->relationLoaded('category') ? $product->category : null;

        return new self(
            name: (string) $item->product_name,
            article: $item->product_article,
            quantity: (int) $item->quantity,
            total_formatted: $item->total_formatted,
            has_image: !empty($product?->img),
            image: $product?->image,
            product_url: $product && $category
                ? save_route('shop.product', [$category->slug, $product->slug_with_id])
                : null,
        );
    }
}
