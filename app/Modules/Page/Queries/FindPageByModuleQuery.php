<?php

namespace App\Modules\Page\Queries;

use App\Modules\Page\Enums\PageModule;
use App\Modules\Page\Models\Page;

readonly class FindPageByModuleQuery
{
    public function __construct(
        private GetAllPagesQuery $getAllPages,
    ) {}

    public function handle(PageModule $module): ?Page
    {
        return $this->getAllPages->handle()
            ->first(fn (Page $page) => $page->module === $module);
    }
}
