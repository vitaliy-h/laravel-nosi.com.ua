<?php

namespace App\Modules\Review\Queries;

use App\Modules\Review\Models\Review;

/**
 * The shop rating summary next to the home page carousel: how many reviews
 * are published and their average.
 */
class GetReviewRatingQuery
{
    /**
     * @return array{rating: float, count: int}
     */
    public function handle(): array
    {
        $row = Review::visible()
            ->selectRaw('COUNT(*) as total, AVG(rating) as average')
            ->first();

        $count = (int) ($row?->total ?? 0);

        return [
            'rating' => $count > 0 ? round((float) $row->average, 1) : 0.0,
            'count'  => $count,
        ];
    }
}
