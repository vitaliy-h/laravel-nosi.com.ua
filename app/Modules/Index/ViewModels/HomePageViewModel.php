<?php

namespace App\Modules\Index\ViewModels;

use App\Modules\Core\Abstract\BaseViewModel;
use App\Modules\Faq\ViewModels\FaqViewModel;
use App\Modules\Promo\ViewModels\PromoViewModel;
use App\Modules\Review\ViewModels\ReviewViewModel;
use App\Modules\Shop\ViewModels\CategoryViewModel;
use App\Modules\Shop\ViewModels\ProductViewModel;
use App\Modules\Slider\ViewModels\SlideViewModel;
use Illuminate\Support\Collection;

/**
 * Every block of the home page design. Nothing is fetched in this class — the
 * controller passes in what the queries returned. Texts that are not records
 * (section titles, benefits, contacts) come from the dictionary in blade; the
 * running line and the footer come from SiteChromeComposer like on every page.
 */
class HomePageViewModel extends BaseViewModel
{
    /**
     * @param  Collection<int, SlideViewModel>  $slides
     * @param  Collection<int, ProductViewModel>  $top_products
     * @param  Collection<int, ProductViewModel>  $new_products
     * @param  Collection<int, ProductViewModel>  $sale_products
     * @param  Collection<int, CategoryViewModel>  $categories
     * @param  Collection<int, PromoViewModel>  $promos
     * @param  bool  $reviews_enabled  the block (and its «leave a review» button) shows even before the first review is published
     * @param  Collection<int, ReviewViewModel>  $reviews
     * @param  Collection<int, FaqViewModel>  $faqs
     */
    public function __construct(
        public readonly Collection $slides,
        public readonly Collection $top_products,
        public readonly Collection $new_products,
        public readonly Collection $sale_products,
        public readonly Collection $categories,
        public readonly Collection $promos,
        public readonly bool $reviews_enabled,
        public readonly Collection $reviews,
        public readonly string $rating_value,
        public readonly float $rating_stars,
        public readonly string $reviews_total,
        public readonly Collection $faqs,
    ) {}
}
