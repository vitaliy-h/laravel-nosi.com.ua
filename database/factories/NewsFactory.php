<?php

namespace Database\Factories;

use App\Modules\News\Models\News;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<News>
 */
class NewsFactory extends Factory
{
    protected $model = News::class;

    public function definition(): array
    {
        return [
            'display'      => true,
            'title'        => fake()->sentence(4),
            'slug'         => fake()->unique()->slug(),
            'short_text'   => fake()->paragraph(),
            'content'      => null,
            'img'          => null,
            'published_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'meta_title'   => null,
            'meta_description' => null,
        ];
    }

    public function invisible(): static
    {
        return $this->state(fn (array $attributes) => [
            'display' => false,
        ]);
    }
}
