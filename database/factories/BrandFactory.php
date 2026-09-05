<?php

namespace Database\Factories;

use App\Modules\Shop\Models\Brand;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Brand>
 */
class BrandFactory extends Factory
{
    /**
     * @var class-string<Brand>
     */
    protected $model = Brand::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'display' => true,
            'title'   => fake()->company(),
            'slug'    => fake()->unique()->slug(),
            'img'     => null,
        ];
    }
}
