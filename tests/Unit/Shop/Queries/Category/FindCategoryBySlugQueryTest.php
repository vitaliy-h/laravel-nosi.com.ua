<?php

namespace Tests\Unit\Shop\Queries\Category;

use App\Modules\Shop\Models\Category;
use App\Modules\Shop\Queries\Category\FindCategoryBySlugQuery;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FindCategoryBySlugQueryTest extends TestCase
{
    use RefreshDatabase;

    private FindCategoryBySlugQuery $query;

    protected function setUp(): void
    {
        parent::setUp();

        $this->query = app(FindCategoryBySlugQuery::class);
    }

    public function test_get_category_by_slug_returns_visible_category(): void
    {
        $category = Category::factory()->create(['slug' => 'electronics']);

        $result = $this->query->handle('electronics');

        $this->assertSame($category->id, $result->id);
        $this->assertSame('electronics', $result->slug);
    }

    public function test_get_category_by_slug_throws_for_invisible_category(): void
    {
        Category::factory()->invisible()->create(['slug' => 'hidden']);

        $this->expectException(ModelNotFoundException::class);

        $this->query->handle('hidden');
    }

    public function test_get_category_by_slug_throws_for_nonexistent_slug(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->query->handle('does-not-exist');
    }
}
