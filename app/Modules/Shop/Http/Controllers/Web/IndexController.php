<?php

namespace App\Modules\Shop\Http\Controllers\Web;

use App\Modules\ShopBasket\Actions\FindBasketAction;
use App\Modules\Core\Abstract\BaseWebController;
use App\Modules\Core\Dtos\BreadcrumbData;
use App\Modules\Shop\Dtos\ProductSearchDto;
use App\Modules\Shop\Queries\Category\FindCategoryBySlugQuery;
use App\Modules\Shop\Queries\Category\GetAllCategoriesQuery;
use App\Modules\Shop\Queries\Category\GetCategoryBreadcrumbsQuery;
use App\Modules\Shop\Queries\Product\FindProductByIdQuery;
use App\Modules\Shop\Queries\Product\PrepareShopFilterQuery;
use App\Modules\Shop\Queries\Product\SearchProductsQuery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IndexController extends BaseWebController
{
    public function __construct(
        private readonly GetAllCategoriesQuery $getAllCategories,
        private readonly PrepareShopFilterQuery $prepareShopFilter,
        private readonly SearchProductsQuery $searchProducts,
        private readonly FindCategoryBySlugQuery $findCategoryBySlug,
        private readonly GetCategoryBreadcrumbsQuery $getCategoryBreadcrumbs,
        private readonly FindProductByIdQuery $findProductById,
        private readonly FindBasketAction $findBasket,
    ) {}

    public function index(Request $request)
    {
        $categories = $this->getAllCategories->handle();
        $filter = $this->prepareShopFilter->handle(null, $request);
        $products = $this->searchProducts->handle(new ProductSearchDto(filter: $filter, sort: $request->query('sort'), paginate: true));

        return view('pages.shop.category', compact(
            'categories',
            'products',
            'filter',
        ));
    }

    public function category(string $categorySlug, Request $request)
    {
        $category = $this->findCategoryBySlug->handle($categorySlug);

        view()->share([
            'meta_title' => $category->meta_title ?: $category->title,
            'meta_description' => $category->meta_description,
        ]);

        $categories = $this->getAllCategories->handle($category->id);
        $breadcrumbs = $this->getCategoryBreadcrumbs->handle($category);
        $filter = $this->prepareShopFilter->handle($category, $request);
        $products = $this->searchProducts->handle(new ProductSearchDto(category: $category, filter: $filter, sort: $request->query('sort'), paginate: true));

        return view('pages.shop.category', compact(
            'category',
            'categories',
            'products',
            'filter',
            'breadcrumbs'
        ));
    }

    public function product(string $categorySlug, string $productSlugId, Request $request)
    {
        $category = $this->findCategoryBySlug->handle($categorySlug);

        if (! preg_match('/^(.+)-(\d+)$/', $productSlugId, $matches)) {
            abort(404);
        }

        $productSlug = $matches[1];
        $productId = (int) $matches[2];

        $product = $this->findProductById->handle($productId);

        if ($categorySlug !== $category->slug || $productSlug !== $product->slug) {
            return redirect()->to(
                save_route('shop.product', [$category->slug, $product->slug.'-'.$product->id]),
                301
            );
        }

        view()->share([
            'meta_title' => $product->meta_title ?: $product->title,
            'meta_description' => $product->meta_description,
        ]);

        $breadcrumbs = $this->getCategoryBreadcrumbs->handle($category);
        $breadcrumbs->push(
            new BreadcrumbData(
                title: $product->name ?? $product->title,
                url: save_route('shop.product', [$category->slug, $product->slug_with_id])
            )
        );
        $filter = $this->prepareShopFilter->handle($category, $request);

        $basketQuantity = $this->basketQuantityFor($product->id) ?? 1;

        return view('pages.shop.product', compact(
            'category',
            'product',
            'filter',
            'breadcrumbs',
            'basketQuantity',
        ));
    }

    private function basketQuantityFor(int $productId): ?int
    {
        $basket = $this->findBasket->handle(Auth::user());

        return $basket?->items()->where('shop_product_id', $productId)->value('quantity');
    }
}
