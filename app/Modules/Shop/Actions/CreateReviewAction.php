<?php

namespace App\Modules\Shop\Actions;

use App\Modules\Shop\Dtos\CreateReviewData;
use App\Modules\Shop\Models\Review;

class CreateReviewAction
{
    /**
     * Store a review submitted from the storefront.
     *
     * Always hidden on creation: it becomes visible (and starts counting
     * towards the product rating) only after an admin publishes it.
     */
    public function handle(CreateReviewData $data): Review
    {
        return Review::create([
            'display' => false,
            'product_id' => $data->productId,
            'user_id' => $data->userId,
            'rating' => $data->rating,
            'name' => $data->name,
            'email' => $data->email,
            'text' => $data->text,
            'ip' => $data->ip,
        ]);
    }
}
