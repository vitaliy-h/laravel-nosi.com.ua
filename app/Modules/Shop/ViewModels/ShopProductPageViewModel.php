<?php

namespace App\Modules\Shop\ViewModels;

use App\Modules\Core\Abstract\BaseViewModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ShopProductPageViewModel extends BaseViewModel
{
    /**
     * @param  LengthAwarePaginator<int, ProductReviewViewModel>  $reviews
     */
    public function __construct(
        public readonly ProductViewModel $product,
        public readonly CategoryViewModel $category,
        public readonly int $cart_quantity,
        public readonly LengthAwarePaginator $reviews,
        public readonly ProductRatingViewModel $rating,
    ) {}
}
