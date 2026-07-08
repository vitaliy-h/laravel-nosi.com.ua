<?php

namespace App\Modules\Core\Queries;

use App\Modules\Core\Dtos\BreadcrumbData;
use App\Modules\Core\Enums\PageModule;
use App\Modules\Core\Facades\UrlFacade;
use App\Modules\Core\Models\Page;
use Illuminate\Support\Collection;

readonly class GetPageBreadcrumbsQuery
{
    public function __construct(
        private GetAllPagesQuery $getAllPages,
    ) {}

    public function handle(?Page $currentPage = null): Collection
    {
        $breadcrumbs = $this->getAllPages->handle()
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
