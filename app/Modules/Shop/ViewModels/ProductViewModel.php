<?php

namespace App\Modules\Shop\ViewModels;

use App\Modules\Core\Abstract\BaseViewModel;
use App\Modules\Shop\Models\Category;
use App\Modules\Shop\Models\Product;

class ProductViewModel extends BaseViewModel
{
    public function __construct(
        public readonly int $id,
        public readonly string $title,
        public readonly ?string $url,
        public readonly bool $has_image,
        public readonly string $image,
        public readonly string $thumb_url,
        public readonly ?float $price,
        public readonly ?float $price2,
        public readonly bool $has_discount,
        public readonly ?string $text,
        public readonly ?string $article,
        public readonly float $rating,
        public readonly int $reviews_count,
        public readonly ?CategoryViewModel $category,
        public readonly ?int $root_category_id,
    ) {}

    /**
     * The product's category has to be eager-loaded by the caller: it carries
     * the URL, and on the home page the root ancestor the isotope filter groups
     * by. Without it the product simply loses its link rather than firing a
     * query per card.
     */
    public static function fromProduct(Product $product): self
    {
        $category = $product->relationLoaded('category') ? $product->category : null;

        return new self(
            id: $product->id,
            title: (string) $product->title,
            url: $category
                ? save_route('shop.product', [$category->slug, $product->slug_with_id])
                : null,
            has_image: ! empty($product->img),
            image: $product->image,
            thumb_url: $product->imageUrl('thumb'),
            price: $product->price,
            price2: $product->price2,
            has_discount: (bool) $product->price2,
            text: $product->text,
            article: $product->article,
            rating: (float) $product->rating,
            reviews_count: (int) $product->reviews_count,
            category: $category ? CategoryViewModel::from($category) : null,
            root_category_id: self::rootCategoryId($category),
        );
    }

    /**
     * The top-level category a product hangs under, for the home page filter.
     * Needs `category.ancestors` eager-loaded; without it a nested category
     * reports no root rather than walking the tree per product.
     */
    private static function rootCategoryId(?Category $category): ?int
    {
        if ($category === null) {
            return null;
        }

        if ($category->isRoot()) {
            return $category->id;
        }

        return $category->relationLoaded('ancestors')
            ? $category->ancestors->first()?->id
            : null;
    }
}
