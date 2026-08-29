<?php

namespace App\Modules\Page\Services;

use App\Modules\Page\Models\Page;
use App\Modules\Page\Queries\GetPageBreadcrumbsQuery;
use App\Modules\Page\ViewModels\LayoutViewModel;
use App\Modules\Page\ViewModels\MenuItemViewModel;
use App\Modules\Page\ViewModels\PageViewModel;
use App\Modules\ShopCart\Services\CartService;
use Illuminate\Support\Collection;

readonly class PageContextSharer
{
    public function __construct(
        private GetPageBreadcrumbsQuery $breadcrumbs,
        private MenuService $menu,
        private CartService $cart,
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
            header_menu: $this->menuItems($this->menu->getHeaderMenu()),
            footer_menu: $this->menuItems($this->menu->getFooterMenu()),
            cart_count: $this->cart->getCount(),
        );
    }

    /**
     * @param  Collection<int, Page>  $pages
     * @return Collection<int, MenuItemViewModel>
     */
    private function menuItems(Collection $pages): Collection
    {
        return $pages->map(fn (Page $page) => MenuItemViewModel::from($page))->values();
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
