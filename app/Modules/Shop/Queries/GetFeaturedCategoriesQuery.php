<?php

namespace App\Modules\Shop\Queries;

use App\Modules\Shop\Models\Category;

class GetFeaturedCategoriesQuery
{
    public function handle(int $limit = 8)
    {
        return Category::visible()->whereIsRoot()->orderBy('_lft')->get();
    }
}
