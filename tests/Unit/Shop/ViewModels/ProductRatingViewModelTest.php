<?php

namespace Tests\Unit\Shop\ViewModels;

use App\Modules\Shop\Models\Product;
use App\Modules\Shop\ViewModels\ProductRatingViewModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductRatingViewModelTest extends TestCase
{
    use RefreshDatabase;

    private function rating(float $average, int $total, array $breakdown): ProductRatingViewModel
    {
        $product = Product::factory()->create();
        $product->rating = $average;
        $product->reviews_count = $total;

        return ProductRatingViewModel::forProduct($product, $breakdown);
    }

    public function test_it_formats_the_average_to_one_decimal(): void
    {
        $this->assertSame('4.5', $this->rating(4.5, 2, [])->average_formatted);
        $this->assertSame('0.0', $this->rating(0, 0, [])->average_formatted);
    }

    /**
     * A product with no reviews must not divide by its review count.
     */
    public function test_the_breakdown_of_a_product_without_reviews_is_all_zeroes(): void
    {
        $rating = $this->rating(0, 0, [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0]);

        foreach (range(1, 5) as $stars) {
            $this->assertSame(0.0, $rating->percentFor($stars));
            $this->assertSame(0, $rating->countFor($stars));
        }
    }

    public function test_it_turns_the_breakdown_into_percentages(): void
    {
        $rating = $this->rating(4.0, 4, [5 => 3, 4 => 1]);

        $this->assertSame(75.0, $rating->percentFor(5));
        $this->assertSame(25.0, $rating->percentFor(4));
        $this->assertSame(0.0, $rating->percentFor(3), 'a star nobody gave is absent from the breakdown');
        $this->assertSame(0, $rating->countFor(3));
    }
}
