<?php

namespace Tests\Unit\Shop;

use App\Modules\Shop\Actions\RecalculateProductRatingAction;
use App\Modules\Shop\Models\Product;
use App\Modules\Shop\Models\ProductReview;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecalculateProductRatingActionTest extends TestCase
{
    use RefreshDatabase;

    private function recalculate(Product $product): void
    {
        app(RecalculateProductRatingAction::class)->handle($product);
    }

    public function test_average_and_count_are_built_from_visible_reviews_only(): void
    {
        $product = Product::factory()->create();

        ProductReview::factory()->forProduct($product)->withRating(5)->create();
        ProductReview::factory()->forProduct($product)->withRating(3)->create();
        ProductReview::factory()->forProduct($product)->withRating(1)->invisible()->create();

        $this->recalculate($product);

        $product->refresh();

        $this->assertSame(4.0, $product->rating);
        $this->assertSame(2, $product->reviews_count);
    }

    public function test_hidden_review_does_not_change_aggregates(): void
    {
        $product = Product::factory()->create();

        ProductReview::factory()->forProduct($product)->withRating(4)->create();
        $product->refresh();

        ProductReview::factory()->forProduct($product)->withRating(1)->invisible()->create();

        $product->refresh();

        $this->assertSame(4.0, $product->rating);
        $this->assertSame(1, $product->reviews_count);
    }

    public function test_publishing_a_review_updates_aggregates_through_the_observer(): void
    {
        $product = Product::factory()->create();

        $review = ProductReview::factory()->forProduct($product)->withRating(5)->invisible()->create();

        $this->assertSame(0.0, $product->fresh()->rating);

        $review->update(['display' => true]);

        $product->refresh();

        $this->assertSame(5.0, $product->rating);
        $this->assertSame(1, $product->reviews_count);
    }

    public function test_deleting_a_review_recalculates_aggregates(): void
    {
        $product = Product::factory()->create();

        ProductReview::factory()->forProduct($product)->withRating(2)->create();
        $review = ProductReview::factory()->forProduct($product)->withRating(4)->create();

        $review->delete();

        $product->refresh();

        $this->assertSame(2.0, $product->rating);
        $this->assertSame(1, $product->reviews_count);
    }

    public function test_product_without_visible_reviews_gets_zero_aggregates(): void
    {
        $product = Product::factory()->create();

        ProductReview::factory()->forProduct($product)->withRating(5)->invisible()->create();

        $this->recalculate($product);

        $product->refresh();

        $this->assertSame(0.0, $product->rating);
        $this->assertSame(0, $product->reviews_count);
    }

    public function test_aggregates_update_does_not_touch_product_updated_at(): void
    {
        $product = Product::factory()->create();

        $updatedAt = $product->fresh()->updated_at;

        ProductReview::factory()->forProduct($product)->withRating(5)->create();

        $this->assertEquals($updatedAt, $product->fresh()->updated_at);
        $this->assertSame(5.0, $product->fresh()->rating);
    }
}
