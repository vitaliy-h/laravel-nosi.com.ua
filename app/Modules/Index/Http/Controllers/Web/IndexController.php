<?php

namespace App\Modules\Index\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Modules\Core\Interfaces\ModuleIndexControllerInterface;
use App\Modules\Shop\Queries\GetBestSellersQuery;
use App\Modules\Shop\Queries\GetFeaturedCategoriesQuery;
use App\Modules\Shop\Queries\GetFeaturedProductsQuery;
use App\Modules\Shop\Queries\GetSpecialCategoriesQuery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class IndexController extends Controller implements ModuleIndexControllerInterface
{
    public function __construct(
        private readonly GetBestSellersQuery $getBestSellers,
        private readonly GetFeaturedCategoriesQuery $getFeaturedCategories,
        private readonly GetFeaturedProductsQuery $getFeaturedProducts,
        private readonly GetSpecialCategoriesQuery $getSpecialCategories,
    ) {}

    public function accessCheck(): RedirectResponse|Response|null
    {
        return null;
    }

    public function index(Request $request, array $segments = [])
    {
        $bestSellers = $this->getBestSellers->handle(4);
        $featuredCategories = $this->getFeaturedCategories->handle(4);
        $featuredProducts = $this->getFeaturedProducts->handle(8);
        $specialCategories = $this->getSpecialCategories->handle(12);

        return view('pages.index', compact('bestSellers', 'featuredCategories', 'featuredProducts', 'specialCategories'));
    }
}
