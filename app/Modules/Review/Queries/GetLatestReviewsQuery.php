<?php

namespace App\Modules\Review\Queries;

use App\Modules\Review\Models\Review;
use Illuminate\Support\Collection;

/**
 * Published reviews, newest first — the home page carousel.
 */
class GetLatestReviewsQuery
{
    /**
     * @return Collection<int, Review>
     */
    public function handle(int $limit = 10): Collection
    {
        return Review::visible()
            ->with('user')
            ->latest()
            ->limit($limit)
            ->get();
    }
}
