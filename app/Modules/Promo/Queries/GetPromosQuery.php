<?php

namespace App\Modules\Promo\Queries;

use App\Modules\Promo\Models\Promo;
use Illuminate\Support\Collection;

class GetPromosQuery
{
    /**
     * The grid is designed for at most three banners.
     *
     * @return Collection<int, Promo>
     */
    public function handle(int $limit = 3): Collection
    {
        return Promo::visible()
            ->orderByDesc('rank')
            ->orderBy('id')
            ->limit($limit)
            ->get();
    }
}
