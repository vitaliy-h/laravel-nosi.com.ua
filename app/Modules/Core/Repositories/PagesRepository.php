<?php

namespace App\Modules\Core\Repositories;

use App\Modules\Core\Dtos\BreadcrumbData;
use App\Modules\Core\Enums\PageModule;
use App\Modules\Core\Facades\UrlFacade;
use App\Modules\Core\Models\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class PagesRepository
{
    private const CACHE_KEY = 'pages.all';

    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    private function getAllPages(): Collection
    {
        $rows = Cache::remember(self::CACHE_KEY, 60 * 60 * 24, static function () {
            return Page::visible()->select(['id', 'title', 'slug', 'module'])->get()->toArray();
        });

        return Page::hydrate($rows);
    }

    public function findBySlug(string $slug): ?Page
    {
        return $this->getAllPages()
            ->filter(fn (Page $page) => $page->module !== null)
            ->first(fn (Page $page) => $slug === ''
                ? $page->module === PageModule::INDEX
                : $page->slug === $slug
            );
    }

    public function findByModule(PageModule $module): ?Page
    {
        return $this->getAllPages()
            ->first(fn (Page $page) => $page->module === $module);
    }

    public function getBreadcrumbs(?Page $currentPage = null): Collection
    {
        $breadcrumbs = $this->getAllPages()
            ->filter(fn (Page $page) => $page->module === PageModule::INDEX)
            ->map(fn (Page $page) => new BreadcrumbData(
                title: $page->title,
                url: UrlFacade::normalizeForLink($page->slug),
            ));

        if ($currentPage !== null && $currentPage->module !== PageModule::INDEX) {
            $breadcrumbs->push(new BreadcrumbData(
                title: $currentPage->title,
                url: UrlFacade::normalizeForLink($currentPage->slug),
            ));
        }
        return $breadcrumbs->values();
    }
}
