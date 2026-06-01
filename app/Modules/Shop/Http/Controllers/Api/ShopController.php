<?php

namespace App\Modules\Shop\Http\Controllers\Api;

use App\Modules\Core\Abstract\BaseApiController;
use App\Modules\Core\Http\Resources\BasePaginationResource;
use App\Modules\Shop\Http\Requests\ShopProductsByCategoryRequest;
use App\Modules\Shop\Http\Resources\CategoryResource;
use App\Modules\Shop\Http\Resources\ProductResource;
use App\Modules\Shop\Repository\ShopRepository;
use Illuminate\Http\JsonResponse;

class ShopController extends BaseApiController
{
    public function __construct(
        private readonly ShopRepository $shopRepository,
    )
    {
    }

    public function categories(): JsonResponse
    {
        $categories = $this->shopRepository->getTree();

        return $this->success(
            ['categories' => CategoryResource::collection($categories)],
            message: t('shop.categories_list')
        );
    }

    public function category(string $slug): JsonResponse
    {
        $category = $this->shopRepository->getCategoryBySlug($slug);

        return $this->success(
            ['category' => new CategoryResource($category)],
            message: t('shop.category_info')
        );
    }

    public function productsByCategory(string $slug, ShopProductsByCategoryRequest $request): JsonResponse
    {
        $category = $this->shopRepository->getCategoryBySlug($slug);
        $products = $this->shopRepository->productsByCategory($category);

        return $this->success([
            'products' => ProductResource::collection($products),
            'pagination' => new BasePaginationResource($products),
        ],
            message: t('shop.category_products'));
    }

    public function product(string $categorySlug, string $productSlug): JsonResponse
    {
        $category = $this->shopRepository->getCategoryBySlug($categorySlug);
        $product = $this->shopRepository->getProductBySlug($productSlug, $category);

        return $this->success(
            ['product' => new ProductResource($product)],
            message: t('shop.product_info')
        );
    }
}
