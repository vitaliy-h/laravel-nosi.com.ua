<?php

namespace App\Modules\Shop\Queries\Product;

use App\Modules\Shop\Models\Category;
use App\Modules\Shop\Models\Product;

class FindProductBySlugQuery
{
    /**
     * used in API
     * \App\Modules\Shop\Http\Controllers\Api\ShopController::product
     */
    public function handle(string $slug, Category $category): Product
    {
        return Product::visible()
            ->where('slug', $slug)
            ->where('category_id', $category->id)
            ->with('brand')
            ->firstOrFail();
    }
}
