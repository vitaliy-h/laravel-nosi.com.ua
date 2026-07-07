<?php

namespace Tests\Unit\Shop\Queries;

use App\Modules\Shop\Models\Category;
use App\Modules\Shop\Queries\GetShopCategoryTreeQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetShopCategoryTreeQueryTest extends TestCase
{
    use RefreshDatabase;

    private GetShopCategoryTreeQuery $query;

    protected function setUp(): void
    {
        parent::setUp();

        $this->query = app(GetShopCategoryTreeQuery::class);
    }

    public function test_get_tree_returns_only_visible_root_categories(): void
    {
        $visibleRoot = Category::factory()->create(['slug' => 'visible-root']);
        Category::factory()->invisible()->create(['slug' => 'invisible-root']);

        $tree = $this->query->handle();

        $rootSlugs = collect($tree)->pluck('slug')->all();

        $this->assertContains('visible-root', $rootSlugs);
        $this->assertNotContains('invisible-root', $rootSlugs);
        $this->assertSame($visibleRoot->id, collect($tree)->firstWhere('slug', 'visible-root')->id);
    }
}
