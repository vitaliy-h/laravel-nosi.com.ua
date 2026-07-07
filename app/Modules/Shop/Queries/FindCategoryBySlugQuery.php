<?php

namespace App\Modules\Shop\Queries;

use App\Modules\Shop\Models\Category;

class FindCategoryBySlugQuery
{
    public function handle(string $slug): Category
    {
        return Category::visible()
            ->where('slug', $slug)
            ->firstOrFail();
    }
}
