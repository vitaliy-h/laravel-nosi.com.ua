<?php

namespace Database\Factories;

use App\Modules\Shop\Models\Category;
use App\Modules\Shop\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * @var class-string<Product>
     */
    protected $model = Product::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'display'     => true,
            'category_id' => Category::factory(),
            'brand_id'    => null,
            'price'       => fake()->randomFloat(2, 1, 1000),
            'title'       => fake()->words(3, true),
            'slug'        => fake()->unique()->slug(),
            'article'     => fake()->bothify('ART-####'),
            'img'         => null,
            'text'        => null,
        ];
    }

    public function forCategory(Category $category): static
    {
        return $this->state(fn (array $attributes) => [
            'category_id' => $category->id,
        ]);
    }

    public function invisible(): static
    {
        return $this->state(fn (array $attributes) => [
            'display' => false,
        ]);
    }
}
