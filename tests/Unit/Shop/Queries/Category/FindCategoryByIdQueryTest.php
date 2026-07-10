<?php

namespace Tests\Unit\Shop\Queries\Category;

use App\Modules\Shop\Models\Category;
use App\Modules\Shop\Queries\Category\FindCategoryByIdQuery;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FindCategoryByIdQueryTest extends TestCase
{
    use RefreshDatabase;

    private FindCategoryByIdQuery $query;

    protected function setUp(): void
    {
        parent::setUp();

        $this->query = app(FindCategoryByIdQuery::class);
    }

    public function test_get_category_by_id_returns_visible_category(): void
    {
        $category = Category::factory()->create(['slug' => 'electronics']);

        $result = $this->query->handle($category->id);

        $this->assertSame($category->id, $result->id);
        $this->assertSame('electronics', $result->slug);
    }

    public function test_get_category_by_id_throws_for_invisible_category(): void
    {
        $category = Category::factory()->invisible()->create();

        $this->expectException(ModelNotFoundException::class);

        $this->query->handle($category->id);
    }

    public function test_get_category_by_id_throws_for_nonexistent_id(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->query->handle(999999);
    }
}
