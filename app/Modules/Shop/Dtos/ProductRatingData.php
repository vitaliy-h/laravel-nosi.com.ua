<?php

namespace App\Modules\Shop\Dtos;

use App\Modules\Shop\Models\Product;
use Spatie\LaravelData\Data;

class ProductRatingData extends Data
{
    /**
     * @param  array<int, int>  $breakdown  reviews per star, keyed 5 → 1
     */
    public function __construct(
        public readonly float $average,
        public readonly int $total,
        public readonly array $breakdown,
    ) {}

    /**
     * @param  array<int, int>  $breakdown
     */
    public static function forProduct(Product $product, array $breakdown): self
    {
        return new self(
            average: (float) $product->rating,
            total: (int) $product->reviews_count,
            breakdown: $breakdown,
        );
    }

    /**
     * Width of the breakdown progress bar for the given star, in percent.
     */
    public function percentFor(int $stars): float
    {
        if ($this->total === 0) {
            return 0.0;
        }

        return round(($this->breakdown[$stars] ?? 0) / $this->total * 100, 2);
    }

    public function countFor(int $stars): int
    {
        return $this->breakdown[$stars] ?? 0;
    }

    public function fullStars(): int
    {
        return (int) floor($this->average);
    }

    public function hasHalfStar(): bool
    {
        return ($this->average - $this->fullStars()) >= 0.5;
    }
}
