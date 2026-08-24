<?php

namespace App\Modules\Shop\database\seeders;

use App\Modules\Shop\Actions\RecalculateProductRatingAction;
use App\Modules\Shop\Models\Product;
use App\Modules\Shop\Models\ProductReview;
use Illuminate\Database\Seeder;

class ShopProductReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ProductReview::truncate();

        $products = Product::all();

        foreach ($products as $product) {
            ProductReview::factory()
                ->count(fake()->numberBetween(2, 5))
                ->forProduct($product)
                ->create();
        }

        // truncate() fires no model events, so the aggregates are rebuilt here
        // instead of relying on the observer.
        $recalculate = app(RecalculateProductRatingAction::class);

        foreach ($products as $product) {
            $recalculate->handle($product);
        }
    }
}
