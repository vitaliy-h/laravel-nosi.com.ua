<?php

namespace Tests\Feature\Shop;

use App\Modules\Shop\Models\Category;
use App\Modules\Shop\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShopCatalogApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_categories_index_returns_only_visible_categories(): void
    {
        Category::factory()->create(['slug' => 'visible-cat']);
        Category::factory()->invisible()->create(['slug' => 'hidden-cat']);

        $response = $this->getJson('/api/shop/categories');

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'categories',
            ],
        ]);

        $slugs = collect($response->json('data.categories'))->pluck('slug')->all();

        $this->assertContains('visible-cat', $slugs);
        $this->assertNotContains('hidden-cat', $slugs);
    }

    public function test_category_show_returns_visible_category(): void
    {
        $category = Category::factory()->create(['slug' => 'electronics']);

        $response = $this->getJson('/api/shop/categories/electronics');

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.category.slug', 'electronics');
        $response->assertJsonPath('data.category.id', $category->id);
    }

    public function test_category_show_returns_404_for_nonexistent_slug(): void
    {
        $response = $this->getJson('/api/shop/categories/missing-slug');

        $response->assertStatus(404);
        $response->assertJsonPath('success', false);
    }

    public function test_category_show_returns_404_for_invisible_category(): void
    {
        Category::factory()->invisible()->create(['slug' => 'hidden']);

        $response = $this->getJson('/api/shop/categories/hidden');

        $response->assertStatus(404);
        $response->assertJsonPath('success', false);
    }

    public function test_category_products_returns_products_and_pagination(): void
    {
        $category = Category::factory()->create(['slug' => 'gadgets']);
        Product::factory()->count(2)->forCategory($category)->create();

        $response = $this->getJson('/api/shop/categories/gadgets/products');

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'products',
                'pagination' => [
                    'total',
                    'per_page',
                    'current_page',
                    'last_page',
                    'from',
                    'to',
                ],
            ],
        ]);
        $response->assertJsonCount(2, 'data.products');
        $response->assertJsonPath('data.pagination.total', 2);
    }

    public function test_category_products_excludes_invisible_products(): void
    {
        $category = Category::factory()->create(['slug' => 'gadgets']);
        Product::factory()->forCategory($category)->create(['slug' => 'visible-gadget']);
        Product::factory()->invisible()->forCategory($category)->create(['slug' => 'hidden-gadget']);

        $response = $this->getJson('/api/shop/categories/gadgets/products');

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonCount(1, 'data.products');
        $response->assertJsonPath('data.pagination.total', 1);
        $response->assertJsonPath('data.products.0.slug', 'visible-gadget');
    }

    public function test_product_show_returns_product_for_matching_category_and_slug(): void
    {
        $category = Category::factory()->create(['slug' => 'phones']);
        $product = Product::factory()->forCategory($category)->create(['slug' => 'iphone']);

        $response = $this->getJson('/api/shop/categories/phones/products/iphone');

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.product.slug', 'iphone');
        $response->assertJsonPath('data.product.id', $product->id);
    }

    public function test_product_show_returns_404_when_product_belongs_to_another_category(): void
    {
        $category = Category::factory()->create(['slug' => 'phones']);
        $otherCategory = Category::factory()->create(['slug' => 'tablets']);
        Product::factory()->forCategory($otherCategory)->create(['slug' => 'ipad']);

        $response = $this->getJson('/api/shop/categories/phones/products/ipad');

        $response->assertStatus(404);
        $response->assertJsonPath('success', false);
    }

    public function test_product_show_returns_404_for_invisible_product(): void
    {
        $category = Category::factory()->create(['slug' => 'phones']);
        Product::factory()->invisible()->forCategory($category)->create(['slug' => 'hidden-phone']);

        $response = $this->getJson('/api/shop/categories/phones/products/hidden-phone');

        $response->assertStatus(404);
        $response->assertJsonPath('success', false);
    }
}
