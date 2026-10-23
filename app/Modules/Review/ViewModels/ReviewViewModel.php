<?php

namespace App\Modules\Review\ViewModels;

use App\Modules\Core\Abstract\BaseViewModel;
use App\Modules\Review\Models\Review;

/**
 * A review as the home page carousel shows it: author (with the initial for
 * the avatar), city, stars and text. `user` should be eager-loaded — it
 * supplies the author name of account reviews.
 */
class ReviewViewModel extends BaseViewModel
{
    public function __construct(
        public readonly string $author,
        public readonly string $initial,
        public readonly ?string $city,
        public readonly int $rating,
        public readonly ?string $text,
    ) {}

    public static function fromReview(Review $review): self
    {
        $author = (string) $review->author_name;

        return new self(
            author: $author,
            initial: mb_strtoupper(mb_substr($author, 0, 1)),
            city: $review->city ?: null,
            rating: (int) $review->rating,
            text: $review->text,
        );
    }
}
