<?php

namespace App\Modules\TextPage\Queries;

use App\Modules\Core\Enums\PageModule;
use App\Modules\Core\Models\Page;

class FindTextPageBySlugQuery
{
    public function handle(string $slug): ?Page
    {
        return Page::visible()
            ->where('module', PageModule::TEXT)
            ->where('slug', $slug)
            ->first();
    }
}
