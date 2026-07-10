<?php

namespace App\Modules\Shop\Http\Controllers\Api;

use App\Modules\Core\Abstract\BaseApiController;
use App\Modules\Core\Http\Resources\BasePaginationResource;
use App\Modules\Shop\Dtos\CategorySearchDto;
use App\Modules\Shop\Dtos\ProductSearchDto;
use App\Modules\Shop\Http\Requests\ShopCategoriesRequest;
use App\Modules\Shop\Http\Requests\ShopProductsRequest;
use App\Modules\Shop\Http\Resources\CategoryResource;
use App\Modules\Shop\Http\Resources\ProductResource;
use App\Modules\Shop\Queries\Category\FindCategoryByIdQuery;
use App\Modules\Shop\Queries\Category\SearchCategoriesQuery;
use App\Modules\Shop\Queries\Product\FindProductByIdQuery;
use App\Modules\Shop\Queries\Product\PrepareShopFilterQuery;
use App\Modules\Shop\Queries\Product\SearchProductsQuery;
use Illuminate\Http\JsonResponse;

class ShopController extends BaseApiController
{
    public function __construct(
        private readonly SearchCategoriesQuery $searchCategories,
        private readonly FindCategoryByIdQuery $findCategoryById,
        private readonly PrepareShopFilterQuery $prepareShopFilter,
        private readonly SearchProductsQuery $searchProducts,
        private readonly FindProductByIdQuery $findProductById,
    ) {}

    public function categories(ShopCategoriesRequest $request): JsonResponse
    {
        $categories = $this->searchCategories->handle(new CategorySearchDto(
            root: true,
            hasImage: $request->boolean('has_image'),
            onlyWithProducts: $request->boolean('only_with_products'),
            random: $request->boolean('random'),
            limit: $request->integer('limit') ?: null,
        ));

        return $this->success(
            ['categories' => CategoryResource::collection($categories)],
            message: t('shop.categories_list')
        );
    }

    public function category(int $id): JsonResponse
    {
        $category = $this->findCategoryById->handle($id);

        return $this->success(
            ['category' => new CategoryResource($category)],
            message: t('shop.category_info')
        );
    }

    public function products(ShopProductsRequest $request): JsonResponse
    {
        $category = $request->integer('category_id')
            ? $this->findCategoryById->handle($request->integer('category_id'))
            : null;
        $filter = $this->prepareShopFilter->handle($category, $request);
        $products = $this->searchProducts->handle(new ProductSearchDto(category: $category, filter: $filter, sort: $request->query('sort'), paginate: true));

        return $this->success([
            'products' => ProductResource::collection($products),
            'pagination' => new BasePaginationResource($products),
        ],
            message: t('shop.products_list'));
    }

    public function product(int $id): JsonResponse
    {
        $product = $this->findProductById->handle($id);

        return $this->success(
            ['product' => new ProductResource($product)],
            message: t('shop.product_info')
        );
    }
}
