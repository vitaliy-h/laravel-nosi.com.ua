<?php

namespace App\Modules\Shop\Queries;

use App\Modules\Shop\Models\Product;

class GetBestSellersQuery
{
    public function handle(int $limit = 4)
    {
        return Product::visible()->with('category')->inRandomOrder()->limit($limit)->get();
    }
}
