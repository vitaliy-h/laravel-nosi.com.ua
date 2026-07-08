<?php

namespace App\Modules\Shop\Queries;

use App\Modules\Shop\Models\Category;

class GetShopCategoryTreeQuery
{
    /**
     * used in API
     * \App\Modules\Shop\Http\Controllers\Api\ShopController::categories
     */
    public function handle()
    {
        return Category::visible()->get()->toTree();
    }
}
