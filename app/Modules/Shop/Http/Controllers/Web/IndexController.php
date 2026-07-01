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
    public function __construct(private readonly ShopRepository $repository)
    {
    }

    public function accessCheck(): RedirectResponse|Response|null
    {
        return null;
    }

    public function index(Request $request, array $segments = [])
    {
        if (!empty($segments)) {
            $categorySlug = $segments[0] ?? null;
            $productSlugId = $segments[1] ?? null;

            if ($categorySlug && $productSlugId) {
                return $this->product($categorySlug, $productSlugId, $request);
            }

            if ($categorySlug) {
                return $this->category($categorySlug, $request);
            }
        }

        $categories = $this->repository->allCategories();
        $filter = $this->repository->prepareFilter(null, $request);
        $products = $this->repository->productsByCategory(null, $filter);

        return view('pages.shop.category', compact(
            'categories',
            'products',
            'filter',
        ));
    }
    public function category(string $categorySlug, Request $request)
    {
        $category = $this->repository->getCategoryBySlug($categorySlug);

        view()->share([
            'meta_title' => $category->meta_title ?: $category->title,
            'meta_description' => $category->meta_description,
        ]);

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
    public function product(string $categorySlug, string $productSlugId, Request $request)
    {
        $category = $this->repository->getCategoryBySlug($categorySlug);

        if (!preg_match('/^(.+)-(\d+)$/', $productSlugId, $matches)) {
            abort(404);
        }

        $productSlug = $matches[1];
        $productId = (int)$matches[2];

        $product = $this->repository->getProductById($productId);

        if ($categorySlug !== $category->slug || $productSlug !== $product->slug) {
            return redirect()->to(
                module_url('shop', [
                    $category->slug,
                    $product->slug . '-' . $product->id,
                ]),
                301
            );
        }

        view()->share([
            'meta_title' => $product->meta_title ?: $product->title,
            'meta_description' => $product->meta_description,
        ]);

        $breadcrumbs = $this->repository->breadcrumbs($category);
        $breadcrumbs->push(
            new BreadcrumbData(
                title: $product->name ?? $product->title,
                url: module_url('shop', [$category->slug, $product->slug_with_id])
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
