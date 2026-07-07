<?php

namespace Tests\Unit\Shop\Queries;

use App\Modules\Shop\Models\Category;
use App\Modules\Shop\Models\Product;
use App\Modules\Shop\Queries\GetProductsByCategoryQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class GetProductsByCategoryQueryTest extends TestCase
{
    use RefreshDatabase;

    private GetProductsByCategoryQuery $query;

    protected function setUp(): void
    {
        parent::setUp();

        $this->query = app(GetProductsByCategoryQuery::class);
    }

    public function test_products_by_category_returns_paginator_with_only_category_products(): void
    {
        $category = Category::factory()->create();
        $otherCategory = Category::factory()->create();

        Product::factory()->count(3)->forCategory($category)->create();
        Product::factory()->forCategory($otherCategory)->create();

        $result = $this->query->handle($category);

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertSame(3, $result->total());
        $this->assertTrue(
            collect($result->items())->every(fn (Product $product) => $product->category_id === $category->id)
        );
    }

    public function test_products_by_category_excludes_invisible_products(): void
    {
        $category = Category::factory()->create();

        Product::factory()->count(2)->forCategory($category)->create();
        Product::factory()->invisible()->forCategory($category)->create();

        $result = $this->query->handle($category);

        $this->assertSame(2, $result->total());
        $this->assertTrue(
            collect($result->items())->every(fn (Product $product) => $product->display)
        );
    }
}
