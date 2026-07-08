<?php

namespace App\Modules\Shop\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Modules\Basket\Actions\FindBasketAction;
use App\Modules\Core\Dtos\BreadcrumbData;
use App\Modules\Core\Interfaces\ModuleIndexControllerInterface;
use App\Modules\Shop\Queries\FindCategoryBySlugQuery;
use App\Modules\Shop\Queries\FindProductByIdQuery;
use App\Modules\Shop\Queries\GetAllCategoriesQuery;
use App\Modules\Shop\Queries\GetCategoryBreadcrumbsQuery;
use App\Modules\Shop\Queries\GetProductsByCategoryQuery;
use App\Modules\Shop\Queries\PrepareShopFilterQuery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class IndexController extends Controller implements ModuleIndexControllerInterface
{
    public function __construct(
        private readonly GetAllCategoriesQuery $getAllCategories,
        private readonly PrepareShopFilterQuery $prepareShopFilter,
        private readonly GetProductsByCategoryQuery $getProductsByCategory,
        private readonly FindCategoryBySlugQuery $findCategoryBySlug,
        private readonly GetCategoryBreadcrumbsQuery $getCategoryBreadcrumbs,
        private readonly FindProductByIdQuery $findProductById,
        private readonly FindBasketAction $findBasket,
    ) {}

    public function accessCheck(): RedirectResponse|Response|null
    {
        return null;
    }

    public function index(Request $request, array $segments = [])
    {
        if (! empty($segments)) {
            $categorySlug = $segments[0] ?? null;
            $productSlugId = $segments[1] ?? null;

            if ($categorySlug && $productSlugId) {
                return $this->product($categorySlug, $productSlugId, $request);
            }

            if ($categorySlug) {
                return $this->category($categorySlug, $request);
            }
        }

        $categories = $this->getAllCategories->handle();
        $filter = $this->prepareShopFilter->handle(null, $request);
        $products = $this->getProductsByCategory->handle(null, $filter);

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
        $products = $this->getProductsByCategory->handle($category, $filter);

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
                module_url('shop', [
                    $category->slug,
                    $product->slug.'-'.$product->id,
                ]),
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
                url: module_url('shop', [$category->slug, $product->slug_with_id])
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
