<?php

namespace App\Modules\Shop\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Shop\Http\Repository\ShopRepository;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function __construct(private readonly ShopRepository $repository)
    {
    }

    /**
     * Shop main page.
     * URL: /shop
     */
    public function index(Request $request)
    {
        $categoryList = $this->repository->mainCategories();
        //dd($categoryList);
        //die();

        return view('pages.shop.index', compact('categoryList'));
    }

    /**
     * Category page.
     * URL: /shop/{categorySlug}
     */
    public function category(string $categorySlug, Request $request)
    {
        $category = $this->repository->getCategoryBySlug($categorySlug);

        // Redirect to canonical category URL if slug is not correct.
        if ($categorySlug !== $category->slug) {
            return redirect()->route('shop.category', $category->slug, 301);
        }

        view()->share([
            'meta_title' => $category->meta_title ?: $category->title,
            'meta_description' => $category->meta_description,
        ]);

        $breadcrumbs = $this->repository->breadcrumbs($category);

        $categoryList = $this->repository->childCategories($category);

        if ($categoryList->isNotEmpty()) {
            return view('pages.shop.category-list', compact(
                'category',
                'categoryList',
                'breadcrumbs'
            ));
        }

        $filters = $this->repository->prepareFilters($category, $request);
        $products = $this->repository->productsByCategory($category, $filters);

        return view('pages.shop.category', compact(
            'category',
            'products',
            'filters',
            'breadcrumbs'
        ));
    }

    /**
     * Product page.
     * URL: /shop/{categorySlug}/{productSlug}
     */
    public function product(string $categorySlug, string $productSlug, Request $request)
    {
        $category = $this->repository->getCategoryBySlug($categorySlug);
        $product = $this->repository->getProductBySlug($productSlug, $category);

        // Redirect to canonical product URL if category or product slug is not correct.
        if ($categorySlug !== $category->slug || $productSlug !== $product->slug) {
            return redirect()->route('shop.product', [
                'categorySlug' => $category->slug,
                'productSlug' => $product->slug,
            ], 301);
        }

        view()->share([
            'meta_title' => $product->meta_title ?: $product->title,
            'meta_description' => $product->meta_description,
        ]);

        $breadcrumbs = $this->repository->breadcrumbs($category);
        $breadcrumbs[] = [
            'name' => $product->title,
            'url' => route('shop.product', [$category->slug, $product->slug]),
        ];

        return view('pages.shop.product', compact(
            'category',
            'product',
            'breadcrumbs'
        ));
    }
}
