<?php

namespace App\Modules\Shop\ViewModels;

use App\Modules\Core\Abstract\BaseViewModel;
use App\Modules\Shop\Models\ProductReview;

class ProductReviewViewModel extends BaseViewModel
{
    public function __construct(
        public readonly string $author_name,
        public readonly ?string $created_at_formatted,
        public readonly int $rating,
        public readonly ?string $text,
    ) {}

    public static function fromProductReview(ProductReview $review): self
    {
        return new self(
            author_name: (string) $review->author_name,
            created_at_formatted: $review->created_at?->format('m/d/Y'),
            rating: (int) $review->rating,
            text: $review->text,
        );
    }
}
