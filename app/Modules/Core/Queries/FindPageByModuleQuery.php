<?php

namespace App\Modules\Core\Queries;

use App\Modules\Core\Enums\PageModule;
use App\Modules\Core\Models\Page;

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
