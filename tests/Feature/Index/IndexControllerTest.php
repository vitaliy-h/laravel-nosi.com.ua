<?php

namespace Tests\Feature\Index;

use App\Modules\Shop\Models\Category;
use App\Modules\Shop\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndexControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->skipUnlessModules('shop');

        $this->seedPagesForRouting();
    }

    public function test_home_page_returns_200(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewIs('modules.index.index');
    }

    public function test_home_page_renders_with_products_and_categories(): void
    {
        $category = Category::factory()->create();
        Product::factory()->count(3)->forCategory($category)->create();

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewHas('best_sellers');
        $response->assertViewHas('featured_categories');
        $response->assertViewHas('featured_products');
        $response->assertViewHas('special_categories');
    }
}
