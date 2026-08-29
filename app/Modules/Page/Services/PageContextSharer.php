<?php

namespace App\Modules\Page\Services;

use App\Modules\ShopCart\Services\CartService;
use App\Modules\Page\Models\Page;
use App\Modules\Page\Queries\GetPageBreadcrumbsQuery;

readonly class PageContextSharer
{
    public function __construct(
        private GetPageBreadcrumbsQuery $breadcrumbs,
        private MenuService $menu,
        private CartService $cart,
    ) {}

    /**
     * Share the standard CMS page variables (page, breadcrumbs, meta, menus, cart count)
     * with the view layer.
     */
    public function share(Page $page): void
    {
        view()->share([
            'page' => $page,
            'breadcrumbs' => $this->breadcrumbs->handle($page),
            'meta_title' => $page->meta_title,
            'meta_description' => $page->meta_description,
            'headerMenu' => $this->menu->getHeaderMenu(),
            'footerMenu' => $this->menu->getFooterMenu(),
            'cartCount' => $this->cart->getCount(),
        ]);
    }
}
