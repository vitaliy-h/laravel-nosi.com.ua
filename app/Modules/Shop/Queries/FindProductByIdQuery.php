<?php

namespace App\Modules\Shop\Queries;

use App\Modules\Shop\Models\Product;

class FindProductByIdQuery
{
    public function handle(int $id): Product
    {
        return Product::visible()
            ->where('id', $id)
            ->firstOrFail();
    }
}
