<?php

namespace App\Modules\Shop\Http\Controllers\Api;

use App\Modules\Core\Abstract\BaseApiController;
use App\Modules\Core\Http\Resources\BasePaginationResource;
use App\Modules\Shop\Dtos\CategorySearchData;
use App\Modules\Shop\Dtos\ProductSearchData;
use App\Modules\Shop\Dtos\ShopFilterCriteriaData;
use App\Modules\Shop\Enums\SortOptionEnum;
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
use Illuminate\Http\Request;

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
        $categories = $this->searchCategories->handle(new CategorySearchData(
            root: true,
            hasImage: $request->boolean('has_image'),
            onlyWithProducts: $request->boolean('only_with_products'),
            random: $request->boolean('random'),
            limit: $request->integer('limit') ?: null,
        ));

        $categories = $this->searchCategories->withVisibleChildren($categories);

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
        $filter = $this->prepareShopFilter->handle($category, $this->filterCriteria($request));
        $products = $this->searchProducts->handle(new ProductSearchData(
            category: $category,
            filter: $filter,
            sort: SortOptionEnum::tryFrom((string) $request->query('sort')),
            paginate: true,
            perPage: $request->integer('c') ?: null,
        ));

        return $this->success([
            'products'   => ProductResource::collection($products),
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

    /**
     * The HTTP layer stops here: the Query gets values, not a Request.
     */
    private function filterCriteria(Request $request): ShopFilterCriteriaData
    {
        return ShopFilterCriteriaData::fromQuery(
            brand: $request->query('brand'),
            priceFrom: $request->query('price_from'),
            priceTo: $request->query('price_to'),
            search: $request->query('search'),
            query: $request->query(),
        );
    }
}
