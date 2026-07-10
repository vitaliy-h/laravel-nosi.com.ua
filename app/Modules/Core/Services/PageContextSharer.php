<?php

namespace App\Modules\Core\Services;

use App\Modules\Basket\Services\BasketService;
use App\Modules\Core\Models\Page;
use App\Modules\Core\Queries\GetPageBreadcrumbsQuery;

readonly class PageContextSharer
{
    public function __construct(
        private GetPageBreadcrumbsQuery $breadcrumbs,
        private MenuService $menu,
        private BasketService $basket,
    ) {}

    /**
     * Share the standard CMS page variables (page, breadcrumbs, meta, menus, basket count)
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
            'basketCount' => $this->basket->getCount(),
        ]);
    }
}
