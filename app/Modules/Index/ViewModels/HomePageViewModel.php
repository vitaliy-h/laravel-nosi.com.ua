<?php

namespace App\Modules\Index\ViewModels;

use App\Modules\Core\Abstract\BaseViewModel;
use App\Modules\Shop\ViewModels\CategoryViewModel;
use App\Modules\Shop\ViewModels\ProductViewModel;
use Illuminate\Support\Collection;

/**
 * The home page is built entirely out of shop teasers, so every field here is a
 * Shop ViewModel. Nothing is fetched in this class — the controller passes in
 * what the queries returned.
 */
class HomePageViewModel extends BaseViewModel
{
    /**
     * @param  Collection<int, ProductViewModel>  $best_sellers
     * @param  Collection<int, CategoryViewModel>  $featured_categories
     * @param  Collection<int, ProductViewModel>  $featured_products
     * @param  Collection<int, CategoryViewModel>  $special_categories
     */
    public function __construct(
        public readonly Collection $best_sellers,
        public readonly Collection $featured_categories,
        public readonly Collection $featured_products,
        public readonly Collection $special_categories,
    ) {}
}
