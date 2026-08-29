<?php

namespace App\Modules\PageText\Queries;

use App\Modules\Page\Enums\PageModule;
use App\Modules\Page\Models\Page;

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
