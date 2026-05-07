<?php

namespace App\Modules\Core\Repositories;

use App\Modules\Core\Enums\PageModule;
use App\Modules\Core\Models\Page;

class PagesRepository
{
    public function findBySlug(string $slug): ?Page
    {
        return Page::where('slug', $slug)
            ->visible()
            ->first();
    }

    public function findByModule(PageModule $module): ?Page
    {
        return Page::where('module', $module)
            ->visible()
            ->first();
    }
}
