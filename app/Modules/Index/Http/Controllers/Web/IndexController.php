<?php

namespace App\Modules\Index\Http\Controllers\Web;

use App\Modules\Core\Abstract\BaseWebController;
use App\Modules\Index\ViewModels\HomePageViewModel;
use App\Modules\Shop\Dtos\CategorySearchDto;
use App\Modules\Shop\Dtos\ProductSearchDto;
use App\Modules\Shop\Queries\Category\SearchCategoriesQuery;
use App\Modules\Shop\Queries\Product\SearchProductsQuery;
use App\Modules\Shop\ViewModels\CategoryViewModel;
use App\Modules\Shop\ViewModels\ProductViewModel;
use Illuminate\Http\Request;

class IndexController extends BaseWebController
{
    public function __construct(
        private readonly SearchProductsQuery $searchProducts,
        private readonly SearchCategoriesQuery $searchCategories,
    ) {}

    public function index(Request $request)
    {
        $page = new HomePageViewModel(
            best_sellers: ProductViewModel::collect(
                $this->searchProducts->handle(new ProductSearchDto(random: true, limit: 4, with: ['category']))
            ),
            featured_categories: CategoryViewModel::collect(
                $this->searchCategories->handle(new CategorySearchDto(root: true))
            ),
            // `category.ancestors` is what lets a product report its root category,
            // which the isotope filter groups the grid by.
            featured_products: ProductViewModel::collect(
                $this->searchProducts->handle(new ProductSearchDto(random: true, limit: 8, with: ['category.ancestors']))
            ),
            special_categories: CategoryViewModel::collect(
                $this->searchCategories->handle(new CategorySearchDto(hasImage: true, onlyWithProducts: true, random: true, limit: 12))
            ),
        );

        return $this->render('pages.index', $page->toViewData());
    }
}
