<?php

namespace App\Modules\Core\Queries;

use App\Modules\Core\Enums\PageModule;
use App\Modules\Core\Models\Page;

class FindPageBySlugQuery
{
    public function handle(string $slug): ?Page
    {
        if ($slug === '') {
            return Page::visible()
                ->where('module', PageModule::INDEX)
                ->first();
        }

        return Page::visible()
            ->where('slug', $slug)
            ->first();
    }
}
