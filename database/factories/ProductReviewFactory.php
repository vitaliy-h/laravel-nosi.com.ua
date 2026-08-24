<?php

namespace Database\Factories;

use App\Modules\Core\Models\User;
use App\Modules\Shop\Models\Product;
use App\Modules\Shop\Models\ProductReview;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductReview>
 */
class ProductReviewFactory extends Factory
{
    /**
     * @var class-string<ProductReview>
     */
    protected $model = ProductReview::class;

    /**
     * Visible by default so rating assertions stay short; use invisible() for
     * the moderation queue.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'display' => true,
            'product_id' => Product::factory(),
            'user_id' => null,
            'rating' => fake()->numberBetween(1, 5),
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'text' => fake()->paragraph(),
            'ip' => fake()->ipv4(),
        ];
    }

    public function invisible(): static
    {
        return $this->state(fn (array $attributes) => [
            'display' => false,
        ]);
    }

    public function forProduct(Product $product): static
    {
        return $this->state(fn (array $attributes) => [
            'product_id' => $product->id,
        ]);
    }

    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    public function withRating(int $rating): static
    {
        return $this->state(fn (array $attributes) => [
            'rating' => $rating,
        ]);
    }
}
