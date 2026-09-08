<?php

namespace App\Modules\Page\Observers;

use App\Modules\Core\Support\ModuleManifest;
use App\Modules\Page\Models\Page;
use App\Modules\Page\Queries\GetAllPagesQuery;
use Illuminate\Support\Facades\File;

/**
 * A page's slug is a route prefix (see `docs/starter/routing.md`), so saving one
 * in the admin can move URLs. Everything derived from the page rows is dropped
 * here, in one place: the cached rows, the module manifest built from them, and
 * the route table cached from that manifest.
 */
readonly class PageObserver
{
    public function __construct(
        private GetAllPagesQuery $getAllPages,
        private ModuleManifest $manifest,
    ) {}

    public function created(Page $page): void
    {
        $this->invalidate();
    }

    public function updated(Page $page): void
    {
        $this->invalidate();
    }

    public function deleted(Page $page): void
    {
        $this->invalidate();
    }

    private function invalidate(): void
    {
        $this->getAllPages->clear();

        $this->manifest->refresh();

        File::delete(app()->getCachedRoutesPath());
    }
}
