<?php

namespace Tests\Feature\Shop;

use App\Modules\Shop\Models\Brand;
use App\Modules\Shop\Models\Category;
use App\Modules\Shop\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The filter parameters documented in docs/api/shop.md are read straight off the
 * request by PrepareShopFilterQuery, so ShopProductsRequest has to validate them.
 */
class ShopProductsFilterValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_products_accepts_comma_separated_brand_ids(): void
    {
        $brandA = Brand::factory()->create();
        $brandB = Brand::factory()->create();

        $response = $this->getJson("/api/shop/products?brand={$brandA->id},{$brandB->id}");

        $response->assertStatus(200);
    }

    public function test_products_accepts_brand_as_an_array(): void
    {
        $brand = Brand::factory()->create();

        $response = $this->getJson("/api/shop/products?brand[]={$brand->id}");

        $response->assertStatus(200);
    }

    public function test_products_rejects_non_numeric_brand(): void
    {
        $response = $this->getJson('/api/shop/products?brand=nike');

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('brand');
    }

    public function test_products_rejects_non_integer_brand_in_array(): void
    {
        $response = $this->getJson('/api/shop/products?brand[]=nike');

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('brand.0');
    }

    public function test_products_accepts_a_price_range(): void
    {
        $category = Category::factory()->create();
        Product::factory()->forCategory($category)->create(['price' => 150]);

        $response = $this->getJson('/api/shop/products?price_from=100&price_to=200');

        $response->assertStatus(200);
    }

    public function test_products_rejects_non_numeric_price(): void
    {
        $response = $this->getJson('/api/shop/products?price_from=cheap');

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('price_from');
    }

    public function test_products_rejects_negative_price(): void
    {
        $response = $this->getJson('/api/shop/products?price_from=-1');

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('price_from');
    }

    public function test_products_rejects_price_to_below_price_from(): void
    {
        $response = $this->getJson('/api/shop/products?price_from=200&price_to=100');

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('price_to');
    }

    public function test_products_rejects_a_search_term_over_255_characters(): void
    {
        $response = $this->getJson('/api/shop/products?search=' . str_repeat('a', 256));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('search');
    }

    public function test_products_rejects_page_zero(): void
    {
        $response = $this->getJson('/api/shop/products?page=0');

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('page');
    }
}
