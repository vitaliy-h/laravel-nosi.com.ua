<?php

namespace Tests\Unit\Shop;

use App\Modules\Shop\Models\Category;
use App\Modules\Shop\Models\Product;
use App\Modules\Shop\Repository\ShopRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class ShopRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private ShopRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = app(ShopRepository::class);
    }

    public function test_get_tree_returns_only_visible_root_categories(): void
    {
        $visibleRoot = Category::factory()->create(['slug' => 'visible-root']);
        Category::factory()->invisible()->create(['slug' => 'invisible-root']);

        $tree = $this->repository->getTree();

        $rootSlugs = collect($tree)->pluck('slug')->all();

        $this->assertContains('visible-root', $rootSlugs);
        $this->assertNotContains('invisible-root', $rootSlugs);
        $this->assertSame($visibleRoot->id, collect($tree)->firstWhere('slug', 'visible-root')->id);
    }

    public function test_get_category_by_slug_returns_visible_category(): void
    {
        $category = Category::factory()->create(['slug' => 'electronics']);

        $result = $this->repository->getCategoryBySlug('electronics');

        $this->assertSame($category->id, $result->id);
        $this->assertSame('electronics', $result->slug);
    }

    public function test_get_category_by_slug_throws_for_invisible_category(): void
    {
        Category::factory()->invisible()->create(['slug' => 'hidden']);

        $this->expectException(ModelNotFoundException::class);

        $this->repository->getCategoryBySlug('hidden');
    }

    public function test_get_category_by_slug_throws_for_nonexistent_slug(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->repository->getCategoryBySlug('does-not-exist');
    }

    public function test_get_product_by_slug_returns_visible_product_in_category(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->forCategory($category)->create(['slug' => 'widget']);

        $result = $this->repository->getProductBySlug('widget', $category);

        $this->assertSame($product->id, $result->id);
        $this->assertSame($category->id, $result->category_id);
    }

    public function test_get_product_by_slug_throws_when_product_belongs_to_another_category(): void
    {
        $category = Category::factory()->create();
        $otherCategory = Category::factory()->create();
        Product::factory()->forCategory($otherCategory)->create(['slug' => 'shared-slug']);

        $this->expectException(ModelNotFoundException::class);

        $this->repository->getProductBySlug('shared-slug', $category);
    }

    public function test_get_product_by_slug_throws_for_invisible_product(): void
    {
        $category = Category::factory()->create();
        Product::factory()->invisible()->forCategory($category)->create(['slug' => 'hidden-widget']);

        $this->expectException(ModelNotFoundException::class);

        $this->repository->getProductBySlug('hidden-widget', $category);
    }

    public function test_products_by_category_returns_paginator_with_only_category_products(): void
    {
        $category = Category::factory()->create();
        $otherCategory = Category::factory()->create();

        Product::factory()->count(3)->forCategory($category)->create();
        Product::factory()->forCategory($otherCategory)->create();

        $result = $this->repository->productsByCategory($category);

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

        $result = $this->repository->productsByCategory($category);

        $this->assertSame(2, $result->total());
        $this->assertTrue(
            collect($result->items())->every(fn (Product $product) => $product->display)
        );
    }
}
