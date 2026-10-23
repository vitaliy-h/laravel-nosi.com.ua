<?php

namespace App\Modules\Page\Services;

use App\Modules\Announce\Queries\GetAnnouncesQuery;
use App\Modules\Announce\ViewModels\AnnounceViewModel;
use App\Modules\Core\Support\ModuleGate;
use App\Modules\Shop\Dtos\CategorySearchData;
use App\Modules\Shop\Queries\Category\SearchCategoriesQuery;
use App\Modules\Shop\ViewModels\CategoryViewModel;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Throwable;

/**
 * Data of the site chrome every storefront page shares — the running line
 * above the header and the «Каталог» column of the footer. A view composer
 * (a per-request view sharer, see `.claude/backend.md`), bound to the two
 * partials in AppServiceProvider, so no controller has to remember to pass it.
 *
 * Each read is done once per request and never takes the page down: error
 * pages render the chrome too, possibly with the database unreachable.
 */
class SiteChromeComposer
{
    private ?Collection $announces = null;

    private ?Collection $categories = null;

    public function __construct(
        private readonly GetAnnouncesQuery $getAnnounces,
        private readonly SearchCategoriesQuery $searchCategories,
    ) {}

    public function compose(View $view): void
    {
        $view->with([
            'site_announces' => $this->announces ??= $this->safely(
                fn () => AnnounceViewModel::collect($this->getAnnounces->handle())
            ),
            'footer_categories' => $this->categories ??= $this->safely(
                fn () => ModuleGate::isEnabled('shop')
                    ? CategoryViewModel::collect($this->searchCategories->handle(new CategorySearchData(root: true, limit: 5)))
                    : collect()
            ),
        ]);
    }

    private function safely(callable $read): Collection
    {
        try {
            return $read();
        } catch (Throwable) {
            return collect();
        }
    }
}
