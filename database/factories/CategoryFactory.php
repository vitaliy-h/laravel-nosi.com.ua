<?php

namespace Database\Factories;

use App\Modules\Shop\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    /**
     * @var class-string<Category>
     */
    protected $model = Category::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'display' => true,
            'title' => fake()->words(2, true),
            'slug' => fake()->unique()->slug(),
            'img' => null,
            'text' => null,
        ];
    }

    public function invisible(): static
    {
        return $this->state(fn (array $attributes) => [
            'display' => false,
        ]);
    }
}
