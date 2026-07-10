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

        $response = $this->getJson("/api/shop/categories/{$category->id}");

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.category.slug', 'electronics');
        $response->assertJsonPath('data.category.id', $category->id);
    }

    public function test_category_show_returns_404_for_nonexistent_id(): void
    {
        $response = $this->getJson('/api/shop/categories/999999');

        $response->assertStatus(404);
        $response->assertJsonPath('success', false);
    }

    public function test_category_show_returns_404_for_invisible_category(): void
    {
        $category = Category::factory()->invisible()->create(['slug' => 'hidden']);

        $response = $this->getJson("/api/shop/categories/{$category->id}");

        $response->assertStatus(404);
        $response->assertJsonPath('success', false);
    }

    public function test_products_returns_products_and_pagination(): void
    {
        $category = Category::factory()->create(['slug' => 'gadgets']);
        Product::factory()->count(2)->forCategory($category)->create();

        $response = $this->getJson("/api/shop/products?category_id={$category->id}");

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

    public function test_products_excludes_invisible_products(): void
    {
        $category = Category::factory()->create(['slug' => 'gadgets']);
        Product::factory()->forCategory($category)->create(['slug' => 'visible-gadget']);
        Product::factory()->invisible()->forCategory($category)->create(['slug' => 'hidden-gadget']);

        $response = $this->getJson("/api/shop/products?category_id={$category->id}");

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonCount(1, 'data.products');
        $response->assertJsonPath('data.pagination.total', 1);
        $response->assertJsonPath('data.products.0.slug', 'visible-gadget');
    }

    public function test_products_rejects_nonexistent_category_id(): void
    {
        $response = $this->getJson('/api/shop/products?category_id=999999');

        $response->assertStatus(422);
        $response->assertJsonPath('success', false);
    }

    public function test_products_returns_404_for_invisible_category_id(): void
    {
        $category = Category::factory()->invisible()->create();

        $response = $this->getJson("/api/shop/products?category_id={$category->id}");

        $response->assertStatus(404);
        $response->assertJsonPath('success', false);
    }

    public function test_products_rejects_non_integer_category_id(): void
    {
        $response = $this->getJson('/api/shop/products?category_id=not-a-number');

        $response->assertStatus(422);
        $response->assertJsonPath('success', false);
    }

    public function test_product_show_returns_visible_product(): void
    {
        $category = Category::factory()->create(['slug' => 'phones']);
        $product = Product::factory()->forCategory($category)->create(['slug' => 'iphone']);

        $response = $this->getJson("/api/shop/products/{$product->id}");

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.product.slug', 'iphone');
        $response->assertJsonPath('data.product.id', $product->id);
    }

    public function test_product_show_returns_404_for_invisible_product(): void
    {
        $category = Category::factory()->create(['slug' => 'phones']);
        $product = Product::factory()->invisible()->forCategory($category)->create(['slug' => 'hidden-phone']);

        $response = $this->getJson("/api/shop/products/{$product->id}");

        $response->assertStatus(404);
        $response->assertJsonPath('success', false);
    }

    public function test_product_show_returns_404_for_nonexistent_id(): void
    {
        $response = $this->getJson('/api/shop/products/999999');

        $response->assertStatus(404);
        $response->assertJsonPath('success', false);
    }
}
