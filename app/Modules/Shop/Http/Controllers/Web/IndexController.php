<?php

namespace App\Modules\Shop\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Modules\Core\Dtos\BreadcrumbData;
use App\Modules\Core\Enums\PageModule;
use App\Modules\Core\Interfaces\ModuleIndexControllerInterface;
use App\Modules\Shop\Repository\ShopRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class IndexController extends Controller implements ModuleIndexControllerInterface
{
    /**
    * Inject shop repository with all catalog/product logic.
    */
    public function __construct(private readonly ShopRepository $repository)
    {
    }
    /**
     * Module access check.
     * Currently the shop module is available for everyone.
     */
    public function accessCheck(): RedirectResponse|Response|null
    {
        return null;
    }
    /**
     * Main shop entry point.
     * Routes request either to product page, category page, or catalog root.
     */
    public function index(Request $request, array $segments = [])
    {
        /**
         * Handle nested shop URLs.
         *
         * Examples:
         * /shop/category-slug
         * /shop/category-slug/product-slug-123
         * @todo too many comments :)
         */
        if (!empty($segments)) {
            $categorySlug = $segments[0] ?? null;
            $productSlugId = $segments[1] ?? null;
            /**
             * If both category and product segments exist, show product page.
             */
            if ($categorySlug && $productSlugId) {
                return $this->product($categorySlug, $productSlugId, $request);
            }
            /**
             * If only category segment exists, show category page.
             */
            if ($categorySlug) {
                return $this->category($categorySlug, $request);
            }
        }
        /**
         * Prepare filters and products for the root catalog page.
         */
        $categories = $this->repository->allCategories();
        $filter = $this->repository->prepareFilter(null, $request);
        $products = $this->repository->productsByCategory(null, $filter);

        return view('pages.shop.category', compact(
            'categories',
            'products',
            'filter',
        ));
    }
    /**
     * Show category page by category slug.
     */
    public function category(string $categorySlug, Request $request)
    {
        $category = $this->repository->getCategoryBySlug($categorySlug);
        /**
         * Redirect to canonical category URL if the slug is not exact.
         * @todo if the slug doesn't match, the category won't be found at all — this redirect only makes sense once canonical_url is supported
         */
        if ($categorySlug !== $category->slug) {
            return redirect()->to(module_url(PageModule::SHOP, $category->slug), 301);
        }
        /**
         * Set category meta tags.
         */
        view()->share([
            'meta_title' => $category->meta_title ?: $category->title,
            'meta_description' => $category->meta_description,
        ]);
        /**
         * Prepare breadcrumbs, filters, and products for the category page.
         */
        $categories = $this->repository->allCategories($category->id);
        $breadcrumbs = $this->repository->breadcrumbs($category);
        $filter = $this->repository->prepareFilter($category, $request);
        $products = $this->repository->productsByCategory($category, $filter);

        return view('pages.shop.category', compact(
            'category',
            'categories',
            'products',
            'filter',
            'breadcrumbs'
        ));
    }
    /**
     * Show product page.
     *
     * Product URL format:
     * /shop/category-slug/product-slug-id
     */
    public function product(string $categorySlug, string $productSlugId, Request $request)
    {
        $category = $this->repository->getCategoryBySlug($categorySlug);
        /**
         * Extract product slug and ID from URL segment.
         */
        if (!preg_match('/^(.+)-(\d+)$/', $productSlugId, $matches)) {
            abort(404);
        }

        $productSlug = $matches[1];
        $productId = (int)$matches[2];
        /**
         * Load product by ID.
         * Product ID is the main reliable identifier from the URL.
         */
        $product = $this->repository->getProductById($productId);
        /**
         * Redirect to canonical product URL if category or product slug is not exact.
         */
        if ($categorySlug !== $category->slug || $productSlug !== $product->slug) {
            return redirect()->to(
                module_url(PageModule::SHOP, [
                    $category->slug,
                    $product->slug . '-' . $product->id,
                ]),
                301
            );
        }
        /**
         * Set product meta tags.
         */
        view()->share([
            'meta_title' => $product->meta_title ?: $product->title,
            'meta_description' => $product->meta_description,
        ]);
        /**
         * Prepare breadcrumbs and filters for product page layout.
         * @todo refactored breadcrumbs to use BreadcrumbData throughout, updated \App\Modules\Shop\Repository\ShopRepository::breadcrumbs
         */
        $breadcrumbs = $this->repository->breadcrumbs($category);
        $breadcrumbs->push(
            new BreadcrumbData(
                title:$product->name ?? $product->title,
                url:module_url(PageModule::SHOP, [$category->product->category->slug,$product->product->slug_with_id])
                /* TODO: added slug_with_id getter in app/Modules/Shop/Models/Product.php for convenient {slug}-{id} formatting */
            )
        );
        $filter = $this->repository->prepareFilter($category, $request);

        return view('pages.shop.product', compact(
            'category',
            'product',
            'filter',
            'breadcrumbs'
        ));
    }
}
