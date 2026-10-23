<?php

namespace App\Modules\Review\Actions;

use App\Modules\Review\Dtos\CreateReviewData;
use App\Modules\Review\Models\Review;

class CreateReviewAction
{
    /**
     * Store a review submitted from the storefront.
     *
     * Always hidden on creation: it shows up on the home page (and counts
     * towards the shop rating) only after an admin publishes it.
     */
    public function handle(CreateReviewData $data): Review
    {
        return Review::create([
            'display' => false,
            'user_id' => $data->userId,
            'rating'  => $data->rating,
            'name'    => $data->name,
            'city'    => $data->city,
            'email'   => $data->email,
            'text'    => $data->text,
            'ip'      => $data->ip,
        ]);
    }
}
