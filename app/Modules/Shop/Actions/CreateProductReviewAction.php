<?php

namespace App\Modules\Shop\Actions;

use App\Modules\Shop\Dtos\CreateProductReviewData;
use App\Modules\Shop\Models\ProductReview;

class CreateProductReviewAction
{
    /**
     * Store a review submitted from the storefront.
     *
     * Always hidden on creation: it becomes visible (and starts counting
     * towards the product rating) only after an admin publishes it.
     */
    public function handle(CreateProductReviewData $data): ProductReview
    {
        return ProductReview::create([
            'display'    => false,
            'product_id' => $data->productId,
            'user_id'    => $data->userId,
            'rating'     => $data->rating,
            'name'       => $data->name,
            'email'      => $data->email,
            'text'       => $data->text,
            'ip'         => $data->ip,
        ]);
    }
}
