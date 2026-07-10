<?php

namespace Tests\Unit\Shop\Queries\Product;

use App\Modules\Shop\Models\Category;
use App\Modules\Shop\Models\Product;
use App\Modules\Shop\Queries\Product\FindProductBySlugQuery;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FindProductBySlugQueryTest extends TestCase
{
    use RefreshDatabase;

    private FindProductBySlugQuery $query;

    protected function setUp(): void
    {
        parent::setUp();

        $this->query = app(FindProductBySlugQuery::class);
    }

    public function test_get_product_by_slug_returns_visible_product_in_category(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->forCategory($category)->create(['slug' => 'widget']);

        $result = $this->query->handle('widget', $category);

        $this->assertSame($product->id, $result->id);
        $this->assertSame($category->id, $result->category_id);
    }

    public function test_get_product_by_slug_throws_when_product_belongs_to_another_category(): void
    {
        $category = Category::factory()->create();
        $otherCategory = Category::factory()->create();
        Product::factory()->forCategory($otherCategory)->create(['slug' => 'shared-slug']);

        $this->expectException(ModelNotFoundException::class);

        $this->query->handle('shared-slug', $category);
    }

    public function test_get_product_by_slug_throws_for_invisible_product(): void
    {
        $category = Category::factory()->create();
        Product::factory()->invisible()->forCategory($category)->create(['slug' => 'hidden-widget']);

        $this->expectException(ModelNotFoundException::class);

        $this->query->handle('hidden-widget', $category);
    }
}
