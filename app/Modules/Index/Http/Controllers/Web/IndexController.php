<?php

namespace App\Modules\Index\Http\Controllers\Web;

use App\Modules\Core\Abstract\BaseWebController;
use App\Modules\Shop\Queries\GetBestSellersQuery;
use App\Modules\Shop\Queries\GetFeaturedCategoriesQuery;
use App\Modules\Shop\Queries\GetFeaturedProductsQuery;
use App\Modules\Shop\Queries\GetSpecialCategoriesQuery;
use Illuminate\Http\Request;

class IndexController extends BaseWebController
{
    public function __construct(
        private readonly GetBestSellersQuery $getBestSellers,
        private readonly GetFeaturedCategoriesQuery $getFeaturedCategories,
        private readonly GetFeaturedProductsQuery $getFeaturedProducts,
        private readonly GetSpecialCategoriesQuery $getSpecialCategories,
    ) {}

    public function index(Request $request)
    {
        $bestSellers = $this->getBestSellers->handle(4);
        $featuredCategories = $this->getFeaturedCategories->handle(4);
        $featuredProducts = $this->getFeaturedProducts->handle(8);
        $specialCategories = $this->getSpecialCategories->handle(12);

        return view('pages.index', compact('bestSellers', 'featuredCategories', 'featuredProducts', 'specialCategories'));
    }
}
