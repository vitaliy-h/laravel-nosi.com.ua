<?php

namespace App\Modules\Core\Repositories;

use App\Modules\Core\Enums\PageModule;
use App\Modules\Core\Models\Page;
use Illuminate\Support\Collection;

class PagesRepository
{
    public function findBySlug(string $slug): ?Page
    {
        return Page::where('slug', $slug)
            ->whereNotNull('module')
            ->visible()
            ->first();
    }

    public function findByModule(PageModule $module): ?Page
    {
        return Page::where('module', $module)
            ->visible()
            ->first();
    }

    /**
     * For first time only index page in breadcrumbs
     * @return Collection
     */
    public function getBreadcrumbs(): Collection
    {
        return Page::where('module', PageModule::INDEX)
            ->visible()
            ->get();
    }
}
