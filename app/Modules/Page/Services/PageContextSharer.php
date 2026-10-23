<?php

namespace App\Modules\Page\Services;

use App\Modules\Page\Models\Page;
use App\Modules\Page\Queries\GetPageBreadcrumbsQuery;
use App\Modules\Page\ViewModels\LayoutViewModel;
use App\Modules\Page\ViewModels\MenuItemViewModel;
use App\Modules\Page\ViewModels\PageViewModel;
use App\Modules\Shop\Models\Category;
use App\Modules\ShopCart\Services\CartContext;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Builds the layout every screen shares and hands it to the view layer.
 *
 * It runs once per request (from the ShareModulePageContext middleware, or from a
 * controller for screens with no CMS page behind them), which makes it one of the
 * entry points allowed to read request-scoped state: it injects the Request and
 * asks CartContext for the cart. Everything below it — ViewModels, Queries — is
 * handed the values instead. See `.claude/backend.md`, «Request-scoped state».
 */
readonly class PageContextSharer
{
    public function __construct(
        private GetPageBreadcrumbsQuery $breadcrumbs,
        private MenuService $menu,
        private CartContext $cart,
        private Request $request,
    ) {}

    /**
     * Share the layout of a screen backed by a CMS page.
     */
    public function share(Page $page): void
    {
        $this->apply($this->build($page));
    }

    /**
     * Share the layout of a screen with no CMS page behind it (order
     * confirmation and the like): menus and cart count still have to be there,
     * the page body and breadcrumbs are empty.
     */
    public function shareWithoutPage(): void
    {
        $this->apply($this->build(null));
    }

    private function build(?Page $page): LayoutViewModel
    {
        return new LayoutViewModel(
            page: $page ? PageViewModel::from($page) : null,
            breadcrumbs: $page ? $this->breadcrumbs->handle($page) : collect(),
            meta_title: $page?->meta_title,
            meta_description: $page?->meta_description,
            header_menu: $this->categoryItems($this->menu->getHeaderCategories())
                ->concat($this->menuItems($this->menu->getHeaderMenu()))
                ->values(),
            footer_menu: $this->menuItems($this->menu->getFooterMenu()),
            cart_count: $this->cart->count(),
        );
    }

    /**
     * @param  Collection<int, Page>  $pages
     * @return Collection<int, MenuItemViewModel>
     */
    private function menuItems(Collection $pages): Collection
    {
        $currentPath = $this->currentPath();

        return $pages
            ->map(fn (Page $page) => MenuItemViewModel::fromPage($page, $currentPath))
            ->values();
    }

    /**
     * @param  Collection<int, Category>  $categories
     * @return Collection<int, MenuItemViewModel>
     */
    private function categoryItems(Collection $categories): Collection
    {
        $currentPath = $this->currentPath();

        return $categories
            ->map(fn (Category $category) => MenuItemViewModel::fromCategory($category, $currentPath))
            ->values();
    }

    private function currentPath(): string
    {
        return '/' . ltrim($this->request->path(), '/');
    }

    /**
     * Meta stays a separate shared variable rather than being read off `page`:
     * a module overrides it with its own entity's meta (a news item, a product)
     * after this middleware has already run.
     */
    private function apply(LayoutViewModel $layout): void
    {
        view()->share($layout->toViewData());
    }
}
