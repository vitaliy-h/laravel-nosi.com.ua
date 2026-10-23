<?php

namespace App\Modules\Page\Services;

use App\Modules\Core\Support\ModuleGate;
use App\Modules\Page\Enums\PageModule;
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
        return $this->menuPages('display_menu');
    }

    public function getFooterMenu()
    {
        return $this->menuPages('display_menu_footer');
    }

    /**
     * Pages marked for a menu, skipping those of a switched-off module
     * (config/modules.php) — their URL answers 404.
     */
    private function menuPages(string $flag): Collection
    {
        return Page::query()
            ->visible()
            ->where($flag, true)
            ->whereIn('module', array_map(fn (PageModule $module) => $module->value, PageModule::active()))
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
