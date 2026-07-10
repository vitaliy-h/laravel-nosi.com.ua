<?php

namespace App\Modules\Index\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Modules\Core\Interfaces\ModuleIndexControllerInterface;
use App\Modules\Shop\Dtos\CategorySearchDto;
use App\Modules\Shop\Dtos\ProductSearchDto;
use App\Modules\Shop\Queries\Category\SearchCategoriesQuery;
use App\Modules\Shop\Queries\Product\SearchProductsQuery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class IndexController extends Controller implements ModuleIndexControllerInterface
{
    public function __construct(
        private readonly SearchProductsQuery $searchProducts,
        private readonly SearchCategoriesQuery $searchCategories,
    ) {}

    public function accessCheck(): RedirectResponse|Response|null
    {
        return null;
    }

    public function index(Request $request, array $segments = [])
    {
        $bestSellers = $this->searchProducts->handle(new ProductSearchDto(random: true, limit: 4, with: ['category']));
        $featuredCategories = $this->searchCategories->handle(new CategorySearchDto(root: true));
        $featuredProducts = $this->searchProducts->handle(new ProductSearchDto(random: true, limit: 8, with: ['category.ancestors']));
        $specialCategories = $this->searchCategories->handle(new CategorySearchDto(hasImage: true, onlyWithProducts: true, random: true, limit: 12));

        return view('pages.index', compact('bestSellers', 'featuredCategories', 'featuredProducts', 'specialCategories'));
    }
}
