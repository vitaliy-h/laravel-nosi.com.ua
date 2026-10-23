<?php

namespace App\Modules\Page\Services;

use App\Modules\Core\Support\ModuleGate;
use App\Modules\Page\Models\Page;
use App\Modules\Shop\Dtos\CategorySearchData;
use App\Modules\Shop\Queries\Category\SearchCategoriesQuery;
use Illuminate\Support\Collection;

class MenuService
{
    public function __construct(
        private readonly SearchCategoriesQuery $searchCategories,
    ) {}

    public function getHeaderMenu()
    {
        return Page::query()
            ->visible()
            ->where('display_menu', true)
            ->orderBy('rank', 'DESC')
            ->get();
    }

    public function getFooterMenu()
    {
        return Page::query()
            ->visible()
            ->where('display_menu_footer', true)
            ->orderBy('rank', 'DESC')
            ->get();
    }

    /**
     * Categories marked for the header menu, in tree order; they go before the pages.
     */
    public function getHeaderCategories(): Collection
    {
        return $this->categories(new CategorySearchData(headerMenu: true));
    }

    /**
     * Categories marked for the footer menu — the «Каталог» column, in tree order.
     */
    public function getFooterCategories(): Collection
    {
        return $this->categories(new CategorySearchData(footerMenu: true));
    }

    private function categories(CategorySearchData $criteria): Collection
    {
        return ModuleGate::isEnabled('shop') ? $this->searchCategories->handle($criteria) : collect();
    }
}
