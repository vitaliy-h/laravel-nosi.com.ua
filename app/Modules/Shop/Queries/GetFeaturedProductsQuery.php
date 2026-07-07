<?php

namespace App\Modules\Shop\Queries;

use App\Modules\Shop\Models\Product;

class GetFeaturedProductsQuery
{
    public function handle(int $limit = 8)
    {
        return Product::visible()->with(['category.ancestors'])->inRandomOrder()->limit($limit)->get();
    }
}
