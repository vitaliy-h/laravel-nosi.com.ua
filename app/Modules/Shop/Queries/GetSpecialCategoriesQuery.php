<?php

namespace App\Modules\Shop\Queries;

use App\Modules\Shop\Models\Category;

class GetSpecialCategoriesQuery
{
    public function handle(int $limit = 12)
    {
        return Category::visible()->whereNotNull('img')->withCount('products')->having('products_count', '>', 0)->inRandomOrder()->limit($limit)->get();
    }
}
