<?php

namespace App\Modules\Shop\Http\Controllers\Api;

use App\Modules\Core\Abstract\BaseApiController;
use App\Modules\Core\Http\Resources\BasePaginationResource;
use App\Modules\Shop\Http\Requests\ShopProductsByCategoryRequest;
use App\Modules\Shop\Dtos\ProductSearchDto;
use App\Modules\Shop\Http\Resources\CategoryResource;
use App\Modules\Shop\Http\Resources\ProductResource;
use App\Modules\Shop\Queries\Category\FindCategoryBySlugQuery;
use App\Modules\Shop\Queries\Category\GetShopCategoryTreeQuery;
use App\Modules\Shop\Queries\Product\FindProductBySlugQuery;
use App\Modules\Shop\Queries\Product\SearchProductsQuery;
use Illuminate\Http\JsonResponse;

class ShopController extends BaseApiController
{
    public function __construct(
        private readonly GetShopCategoryTreeQuery $getShopCategoryTree,
        private readonly FindCategoryBySlugQuery $findCategoryBySlug,
        private readonly SearchProductsQuery $searchProducts,
        private readonly FindProductBySlugQuery $findProductBySlug,
    ) {}

    public function categories(): JsonResponse
    {
        $categories = $this->getShopCategoryTree->handle();

        return $this->success(
            ['categories' => CategoryResource::collection($categories)],
            message: t('shop.categories_list')
        );
    }

    public function category(string $slug): JsonResponse
    {
        $category = $this->findCategoryBySlug->handle($slug);

        return $this->success(
            ['category' => new CategoryResource($category)],
            message: t('shop.category_info')
        );
    }

    public function productsByCategory(string $slug, ShopProductsByCategoryRequest $request): JsonResponse
    {
        $category = $this->findCategoryBySlug->handle($slug);
        $products = $this->searchProducts->handle(new ProductSearchDto(category: $category, sort: $request->query('sort'), paginate: true));

        return $this->success([
            'products' => ProductResource::collection($products),
            'pagination' => new BasePaginationResource($products),
        ],
            message: t('shop.category_products'));
    }

    public function product(string $categorySlug, string $productSlug): JsonResponse
    {
        $category = $this->findCategoryBySlug->handle($categorySlug);
        $product = $this->findProductBySlug->handle($productSlug, $category);

        return $this->success(
            ['product' => new ProductResource($product)],
            message: t('shop.product_info')
        );
    }
}
