<?php

namespace Tests\Unit\Shop\Queries\Product;

use App\Modules\Shop\Dtos\ProductSearchDto;
use App\Modules\Shop\Models\Category;
use App\Modules\Shop\Models\Product;
use App\Modules\Shop\Queries\Product\SearchProductsQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class SearchProductsQueryTest extends TestCase
{
    use RefreshDatabase;

    private SearchProductsQuery $query;

    protected function setUp(): void
    {
        parent::setUp();

        $this->query = app(SearchProductsQuery::class);
    }

    public function test_search_by_category_returns_paginator_with_only_category_products(): void
    {
        $category = Category::factory()->create();
        $otherCategory = Category::factory()->create();

        Product::factory()->count(3)->forCategory($category)->create();
        Product::factory()->forCategory($otherCategory)->create();

        $result = $this->query->handle(new ProductSearchDto(category: $category, paginate: true));

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertSame(3, $result->total());
        $this->assertTrue(
            collect($result->items())->every(fn (Product $product) => $product->category_id === $category->id)
        );
    }

    public function test_search_by_category_excludes_invisible_products(): void
    {
        $category = Category::factory()->create();

        Product::factory()->count(2)->forCategory($category)->create();
        Product::factory()->invisible()->forCategory($category)->create();

        $result = $this->query->handle(new ProductSearchDto(category: $category, paginate: true));

        $this->assertSame(2, $result->total());
        $this->assertTrue(
            collect($result->items())->every(fn (Product $product) => $product->display)
        );
    }

    public function test_search_random_limited_returns_collection(): void
    {
        Product::factory()->count(5)->create();

        $result = $this->query->handle(new ProductSearchDto(random: true, limit: 3, with: ['category']));

        $this->assertCount(3, $result);
    }
}
