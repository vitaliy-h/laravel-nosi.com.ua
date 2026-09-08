<?php

namespace App\Modules\Shop\Http\Controllers\Web;

use App\Modules\Core\Abstract\BaseWebController;
use App\Modules\Core\Support\ModuleGate;
use App\Modules\Core\ViewModels\BreadcrumbViewModel;
use App\Modules\Shop\Dtos\ProductSearchData;
use App\Modules\Shop\Dtos\ShopFilterCriteriaData;
use App\Modules\Shop\Enums\SortOptionEnum;
use App\Modules\Shop\Models\Category;
use App\Modules\Shop\Queries\Category\FindCategoryBySlugQuery;
use App\Modules\Shop\Queries\Category\GetAllCategoriesQuery;
use App\Modules\Shop\Queries\Category\GetCategoryBreadcrumbsQuery;
use App\Modules\Shop\Queries\Product\FindProductByIdQuery;
use App\Modules\Shop\Queries\Product\PrepareShopFilterQuery;
use App\Modules\Shop\Queries\Product\SearchProductsQuery;
use App\Modules\Shop\Queries\ProductReview\GetProductRatingBreakdownQuery;
use App\Modules\Shop\Queries\ProductReview\GetReviewsByProductQuery;
use App\Modules\Shop\ViewModels\CategoryViewModel;
use App\Modules\Shop\ViewModels\ProductRatingViewModel;
use App\Modules\Shop\ViewModels\ProductReviewViewModel;
use App\Modules\Shop\ViewModels\ProductViewModel;
use App\Modules\Shop\ViewModels\ShopCategoryPageViewModel;
use App\Modules\Shop\ViewModels\ShopProductPageViewModel;
use App\Modules\ShopCart\Services\CartContext;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class IndexController extends BaseWebController
{
    public function __construct(
        private readonly GetAllCategoriesQuery $getAllCategories,
        private readonly PrepareShopFilterQuery $prepareShopFilter,
        private readonly SearchProductsQuery $searchProducts,
        private readonly FindCategoryBySlugQuery $findCategoryBySlug,
        private readonly GetCategoryBreadcrumbsQuery $getCategoryBreadcrumbs,
        private readonly FindProductByIdQuery $findProductById,
        private readonly CartContext $cart,
        private readonly GetReviewsByProductQuery $getReviewsByProduct,
        private readonly GetProductRatingBreakdownQuery $getProductRatingBreakdown,
    ) {}

    public function index(Request $request)
    {
        $filter = $this->prepareShopFilter->handle(null, $this->filterCriteria($request));

        return $this->render('modules.shop.category', $this->listing(null, $filter, $request));
    }

    public function category(string $categorySlug, Request $request)
    {
        $category = $this->findCategoryBySlug->handle($categorySlug);

        view()->share([
            'meta_title'       => $category->meta_title ?: $category->title,
            'meta_description' => $category->meta_description,
            'breadcrumbs'      => $this->getCategoryBreadcrumbs->handle($category),
        ]);

        $filter = $this->prepareShopFilter->handle($category, $this->filterCriteria($request));

        return $this->render('modules.shop.category', $this->listing($category, $filter, $request));
    }

    public function product(string $categorySlug, string $productSlugId, Request $request)
    {
        $category = $this->findCategoryBySlug->handle($categorySlug);

        if (!preg_match('/^(.+)-(\d+)$/', $productSlugId, $matches)) {
            abort(404);
        }

        $productSlug = $matches[1];
        $productId = (int) $matches[2];

        $product = $this->findProductById->handle($productId);

        if ($categorySlug !== $category->slug || $productSlug !== $product->slug) {
            return redirect()->to(
                save_route('shop.product', [$category->slug, $product->slug . '-' . $product->id]),
                301
            );
        }

        $breadcrumbs = $this->getCategoryBreadcrumbs->handle($category);
        $breadcrumbs->push(
            new BreadcrumbViewModel(
                title: (string) $product->title,
                url: save_route('shop.product', [$category->slug, $product->slug_with_id])
            )
        );

        view()->share([
            'meta_title'       => $product->meta_title ?: $product->title,
            'meta_description' => $product->meta_description,
            'breadcrumbs'      => $breadcrumbs,
        ]);

        $reviews = $this->getReviewsByProduct->handle($product);

        $page = new ShopProductPageViewModel(
            product: ProductViewModel::from($product),
            category: CategoryViewModel::from($category),
            cart_quantity: $this->cartQuantityFor($product->id) ?? 1,
            reviews: ProductReviewViewModel::collect($reviews),
            rating: ProductRatingViewModel::forProduct(
                $product,
                $this->getProductRatingBreakdown->handle($product),
            ),
        );

        return $this->render('modules.shop.product', $page);
    }

    private function listing(?Category $category, $filter, Request $request): ShopCategoryPageViewModel
    {
        $products = $this->searchProducts->handle(new ProductSearchData(
            category: $category,
            filter: $filter,
            sort: SortOptionEnum::tryFrom((string) $request->query('sort')),
            paginate: true,
            perPage: $request->integer('c') ?: null,
        ));

        return new ShopCategoryPageViewModel(
            category: $category ? CategoryViewModel::from($category) : null,
            categories: $this->categoryTree($this->getAllCategories->handle($category?->id)),
            products: ProductViewModel::collect($products),
            filter: $filter,
        );
    }

    /**
     * @param  Collection<int, Category>  $categories
     * @return Collection<int, CategoryViewModel>
     */
    private function categoryTree(Collection $categories): Collection
    {
        return $categories->map(fn (Category $category) => CategoryViewModel::from($category))->values();
    }

    /**
     * How many of this product the visitor already has in the cart, so the
     * quantity input opens on that number.
     *
     * The product page belongs to `shop` but the cart does not: without this
     * guard the page would query a shop_carts table that stops existing once
     * shop_cart is switched off.
     */
    private function cartQuantityFor(int $productId): ?int
    {
        if (!ModuleGate::isEnabled('shop_cart')) {
            return null;
        }

        $cart = $this->cart->current();

        return $cart?->items()->where('shop_product_id', $productId)->value('quantity');
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
        );
    }
}
