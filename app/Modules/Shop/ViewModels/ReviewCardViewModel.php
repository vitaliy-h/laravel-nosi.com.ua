<?php

namespace App\Modules\Shop\ViewModels;

use App\Modules\Core\Abstract\BaseViewModel;
use App\Modules\Shop\Models\ProductReview;

/**
 * A review as the home page carousel shows it: author with city, stars, text
 * and the product it was left on. `product.category` must be eager-loaded.
 */
class ReviewCardViewModel extends BaseViewModel
{
    public function __construct(
        public readonly string $author,
        public readonly int $rating,
        public readonly ?string $text,
        public readonly string $product_title,
        public readonly string $product_image,
        public readonly ?string $product_url,
    ) {}

    public static function fromProductReview(ProductReview $review): self
    {
        $product = ProductViewModel::from($review->product);

        return new self(
            author: implode(', ', array_filter([(string) $review->author_name, (string) $review->city])),
            rating: (int) $review->rating,
            text: $review->text,
            product_title: $product->title,
            product_image: $product->image,
            product_url: $product->url,
        );
    }
}
