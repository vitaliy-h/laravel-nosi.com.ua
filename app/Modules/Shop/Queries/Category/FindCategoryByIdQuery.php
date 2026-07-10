<?php

namespace App\Modules\Shop\Queries\Category;

use App\Modules\Shop\Models\Category;

class FindCategoryByIdQuery
{
    public function handle(int $id): Category
    {
        return Category::visible()
            ->where('id', $id)
            ->firstOrFail();
    }
}
