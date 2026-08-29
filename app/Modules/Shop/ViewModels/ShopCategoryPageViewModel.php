<?php

namespace App\Modules\Shop\ViewModels;

use App\Modules\Core\Abstract\BaseViewModel;
use App\Modules\Shop\Dtos\Filters\ShopFilterDto;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * The shop listing: both the whole catalogue and a single category.
 *
 * `filter` stays a Dto rather than becoming a ViewModel — the same object also
 * builds the product query, so it belongs to the input side.
 */
class ShopCategoryPageViewModel extends BaseViewModel
{
    /**
     * @param  Collection<int, CategoryViewModel>  $categories  sidebar tree
     * @param  LengthAwarePaginator<int, ProductViewModel>  $products
     */
    public function __construct(
        public readonly ?CategoryViewModel $category,
        public readonly Collection $categories,
        public readonly LengthAwarePaginator $products,
        public readonly ShopFilterDto $filter,
    ) {}
}
