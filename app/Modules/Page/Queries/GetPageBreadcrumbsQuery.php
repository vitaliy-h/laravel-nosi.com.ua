<?php

namespace App\Modules\Page\Queries;

use App\Modules\Core\Facades\UrlFacade;
use App\Modules\Core\ViewModels\BreadcrumbViewModel;
use App\Modules\Page\Enums\PageModule;
use App\Modules\Page\Models\Page;
use Illuminate\Support\Collection;

readonly class GetPageBreadcrumbsQuery
{
    public function __construct(
        private GetAllPagesQuery $getAllPages,
    ) {}

    /**
     * @return Collection<int, BreadcrumbViewModel>
     */
    public function handle(?Page $currentPage = null): Collection
    {
        $breadcrumbs = $this->getAllPages->handle()
            ->filter(fn (Page $page) => $page->module === PageModule::INDEX)
            ->map(fn (Page $page) => new BreadcrumbViewModel(
                title: $page->title,
                url: UrlFacade::normalizeForLink($page->slug),
            ));

        if ($currentPage !== null && $currentPage->module !== PageModule::INDEX) {
            $breadcrumbs->push(new BreadcrumbViewModel(
                title: $currentPage->title,
                url: UrlFacade::normalizeForLink($currentPage->slug),
            ));
        }

        return $breadcrumbs->values();
    }
}
