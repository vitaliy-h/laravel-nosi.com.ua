<?php

namespace Tests\Unit\Shop;

use App\Modules\Shop\Models\Product;
use App\Modules\Shop\Models\ProductReview;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductReviewObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_a_review_refreshes_the_product_aggregates(): void
    {
        $product = Product::factory()->create();

        ProductReview::factory()->forProduct($product)->withRating(4)->create();

        $product->refresh();

        $this->assertSame(1, $product->reviews_count);
        $this->assertSame(4.0, $product->rating);
    }

    public function test_deleting_a_review_refreshes_the_product_aggregates(): void
    {
        $product = Product::factory()->create();
        $review = ProductReview::factory()->forProduct($product)->withRating(4)->create();

        $review->delete();

        $product->refresh();

        $this->assertSame(0, $product->reviews_count);
        $this->assertSame(0.0, $product->rating);
    }

    public function test_moving_a_review_refreshes_both_products(): void
    {
        $from = Product::factory()->create();
        $to = Product::factory()->create();

        $review = ProductReview::factory()->forProduct($from)->withRating(5)->create();

        $review->update(['product_id' => $to->id]);

        $this->assertSame(0, $from->fresh()->reviews_count);
        $this->assertSame(1, $to->fresh()->reviews_count);
        $this->assertSame(5.0, $to->fresh()->rating);
    }

    public function test_moving_a_review_refreshes_both_products_with_a_warmed_relation(): void
    {
        $from = Product::factory()->create();
        $to = Product::factory()->create();

        $review = ProductReview::factory()->forProduct($from)->withRating(5)->create();

        // Filament loads the record (and its product) into the edit form before
        // saving, so the observer must not trust the cached relation.
        $review->load('product');

        $review->update(['product_id' => $to->id]);

        $this->assertSame(0, $from->fresh()->reviews_count);
        $this->assertSame(1, $to->fresh()->reviews_count);
        $this->assertSame(5.0, $to->fresh()->rating);
    }

    public function test_hiding_a_review_drops_it_from_the_product_aggregates(): void
    {
        $product = Product::factory()->create();
        $review = ProductReview::factory()->forProduct($product)->withRating(3)->create();

        $review->update(['display' => false]);

        $product->refresh();

        $this->assertSame(0, $product->reviews_count);
        $this->assertSame(0.0, $product->rating);
    }
}
