<?php

namespace App\Modules\News\Queries;

use App\Modules\News\Models\News;

class FindNewsBySlugQuery
{
    public function handle(string $slug): News
    {
        return News::visible()->where('slug', $slug)->firstOrFail();
    }
}
