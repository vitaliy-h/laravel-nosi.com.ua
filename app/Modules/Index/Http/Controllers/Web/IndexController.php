<?php

namespace App\Modules\Index\Http\Controllers\Web;

use App\Modules\Core\Abstract\BaseWebController;
use App\Modules\Core\Support\ModuleGate;
use App\Modules\Faq\Queries\GetFaqsQuery;
use App\Modules\Faq\ViewModels\FaqViewModel;
use App\Modules\Index\ViewModels\HomePageViewModel;
use App\Modules\Promo\Queries\GetPromosQuery;
use App\Modules\Promo\ViewModels\PromoViewModel;
use App\Modules\Review\Queries\GetLatestReviewsQuery;
use App\Modules\Review\Queries\GetReviewRatingQuery;
use App\Modules\Review\ViewModels\ReviewViewModel;
use App\Modules\Shop\Dtos\CategorySearchData;
use App\Modules\Shop\Queries\Category\SearchCategoriesQuery;
use App\Modules\Shop\Queries\Product\GetDiscountedProductsQuery;
use App\Modules\Shop\Queries\Product\GetHitProductsQuery;
use App\Modules\Shop\Queries\Product\GetNewProductsQuery;
use App\Modules\Shop\ViewModels\CategoryViewModel;
use App\Modules\Shop\ViewModels\ProductViewModel;
use App\Modules\Slider\Queries\GetSlidesQuery;
use App\Modules\Slider\ViewModels\SlideViewModel;

class IndexController extends BaseWebController
{
    public function __construct(
        private readonly GetSlidesQuery $slides,
        private readonly GetPromosQuery $promos,
        private readonly GetFaqsQuery $faqs,
        private readonly GetHitProductsQuery $hitProducts,
        private readonly GetNewProductsQuery $newProducts,
        private readonly GetDiscountedProductsQuery $discountedProducts,
        private readonly SearchCategoriesQuery $searchCategories,
        private readonly GetLatestReviewsQuery $latestReviews,
        private readonly GetReviewRatingQuery $reviewRating,
    ) {}

    public function index()
    {
        // The shop tables are not migrated while the module is off — the page
        // keeps answering with its shop blocks simply empty.
        $shop = ModuleGate::isEnabled('shop');
        $reviews = ModuleGate::isEnabled('review');
        $rating = $reviews ? $this->reviewRating->handle() : ['rating' => 0.0, 'count' => 0];

        $page = new HomePageViewModel(
            slides: SlideViewModel::collect($this->slides->handle()),
            top_products: $shop ? ProductViewModel::collect($this->hitProducts->handle()) : collect(),
            new_products: $shop ? ProductViewModel::collect($this->newProducts->handle()) : collect(),
            sale_products: $shop ? ProductViewModel::collect($this->discountedProducts->handle()) : collect(),
            categories: $shop
                ? CategoryViewModel::collect($this->searchCategories->handle(new CategorySearchData(root: true, withProductsInTree: true, limit: 8)))
                : collect(),
            promos: PromoViewModel::collect($this->promos->handle()),
            reviews_enabled: $reviews,
            reviews: $reviews ? ReviewViewModel::collect($this->latestReviews->handle()) : collect(),
            rating_value: number_format($rating['rating'], 1, '.', ''),
            rating_stars: $rating['rating'],
            reviews_total: number_format($rating['count'], 0, ',', ' '),
            faqs: FaqViewModel::collect($this->faqs->handle()),
        );

        return $this->render('modules.index.index', $page);
    }
}
