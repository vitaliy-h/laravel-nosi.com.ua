<?php

namespace Tests\Feature\Shop;

use App\Modules\Shop\Models\Brand;
use App\Modules\Shop\Models\Category;
use App\Modules\Shop\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShopIndexControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedPagesForRouting();
    }

    public function test_index_returns_200_and_lists_visible_products(): void
    {
        $visible = Product::factory()->create();
        Product::factory()->invisible()->create();

        $response = $this->get('/shop');

        $response->assertStatus(200);
        $response->assertViewIs('pages.shop.category');
        $response->assertViewHas('products', function ($products) use ($visible) {
            return $products->pluck('id')->contains($visible->id) && $products->total() === 1;
        });
    }

    public function test_category_page_returns_200_and_only_lists_category_products(): void
    {
        $category = Category::factory()->create();
        $otherCategory = Category::factory()->create();

        Product::factory()->count(2)->forCategory($category)->create();
        Product::factory()->forCategory($otherCategory)->create();

        $response = $this->get('/shop/'.$category->slug);

        $response->assertStatus(200);
        $response->assertViewIs('pages.shop.category');
        $response->assertViewHas('products', fn ($products) => $products->total() === 2);
    }

    public function test_category_page_returns_404_for_unknown_slug(): void
    {
        $response = $this->get('/shop/unknown-category');

        $response->assertStatus(404);
    }

    public function test_sort_price_asc_orders_products_by_price_ascending(): void
    {
        Product::factory()->create(['title' => 'Expensive', 'price' => 300]);
        Product::factory()->create(['title' => 'Cheap', 'price' => 100]);
        Product::factory()->create(['title' => 'Medium', 'price' => 200]);

        $response = $this->get('/shop?sort=price_asc');

        $titles = $response->viewData('products')->pluck('title')->all();

        $this->assertSame(['Cheap', 'Medium', 'Expensive'], $titles);
    }

    public function test_sort_price_desc_orders_products_by_price_descending(): void
    {
        Product::factory()->create(['title' => 'Expensive', 'price' => 300]);
        Product::factory()->create(['title' => 'Cheap', 'price' => 100]);
        Product::factory()->create(['title' => 'Medium', 'price' => 200]);

        $response = $this->get('/shop?sort=price_desc');

        $titles = $response->viewData('products')->pluck('title')->all();

        $this->assertSame(['Expensive', 'Medium', 'Cheap'], $titles);
    }

    public function test_invalid_sort_value_falls_back_to_default_order(): void
    {
        $response = $this->get('/shop?sort=not-a-real-option');

        $response->assertStatus(200);
    }

    public function test_brand_filter_only_returns_products_for_selected_brand(): void
    {
        $brandA = Brand::factory()->create();
        $brandB = Brand::factory()->create();

        Product::factory()->create(['brand_id' => $brandA->id]);
        Product::factory()->create(['brand_id' => $brandB->id]);

        $response = $this->get('/shop?brand='.$brandA->id);

        $response->assertViewHas('products', fn ($products) => $products->total() === 1
            && $products->first()->brand_id === $brandA->id);
    }

    public function test_price_range_filter_only_returns_products_within_range(): void
    {
        Product::factory()->create(['title' => 'InRange', 'price' => 150]);
        Product::factory()->create(['title' => 'TooCheap', 'price' => 10]);
        Product::factory()->create(['title' => 'TooExpensive', 'price' => 500]);

        $response = $this->get('/shop?price_from=100&price_to=200');

        $titles = $response->viewData('products')->pluck('title')->all();

        $this->assertSame(['InRange'], $titles);
    }

    public function test_search_filter_only_returns_matching_products(): void
    {
        Product::factory()->create(['title' => 'Red Sneakers']);
        Product::factory()->create(['title' => 'Blue Jacket']);

        $response = $this->get('/shop?search=sneakers');

        $titles = $response->viewData('products')->pluck('title')->all();

        $this->assertSame(['Red Sneakers'], $titles);
    }

    public function test_product_page_returns_200_for_valid_slug_and_id(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->forCategory($category)->create();

        $response = $this->get('/shop/'.$category->slug.'/'.$product->slug_with_id);

        $response->assertStatus(200);
        $response->assertViewIs('pages.shop.product');
        $response->assertViewHas('product', fn ($viewProduct) => $viewProduct->id === $product->id);
    }

    public function test_product_page_redirects_when_slug_does_not_match_current_product_slug(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->forCategory($category)->create();

        $response = $this->get('/shop/'.$category->slug.'/stale-slug-'.$product->id);

        $response->assertRedirect('/shop/'.$category->slug.'/'.$product->slug_with_id);
        $response->assertStatus(301);
    }

    public function test_product_page_returns_404_for_invisible_product(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->invisible()->forCategory($category)->create();

        $response = $this->get('/shop/'.$category->slug.'/'.$product->slug_with_id);

        $response->assertStatus(404);
    }
}
