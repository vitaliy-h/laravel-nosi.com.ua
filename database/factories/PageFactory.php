<?php

namespace Database\Factories;

use App\Modules\Page\Enums\PageModule;
use App\Modules\Page\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Page>
 */
class PageFactory extends Factory
{
    /**
     * @var class-string<Page>
     */
    protected $model = Page::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'display' => true,
            'display_menu' => false,
            'display_menu_footer' => false,
            'title' => fake()->words(2, true),
            'slug' => fake()->unique()->slug(),
            'module' => PageModule::TEXT,
            'rank' => fake()->numberBetween(1, 100),
            'content' => null,
            'meta_title' => null,
            'meta_description' => null,
        ];
    }

    public function forModule(PageModule $module): static
    {
        return $this->state(fn (array $attributes) => [
            'module' => $module,
        ]);
    }

    public function withSlug(string $slug): static
    {
        return $this->state(fn (array $attributes) => [
            'slug' => $slug,
        ]);
    }

    public function invisible(): static
    {
        return $this->state(fn (array $attributes) => [
            'display' => false,
        ]);
    }
}
