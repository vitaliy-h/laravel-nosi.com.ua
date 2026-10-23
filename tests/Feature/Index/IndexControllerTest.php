<?php

namespace Tests\Feature\Index;

use App\Modules\Announce\Models\Announce;
use App\Modules\Faq\Models\Faq;
use App\Modules\Shop\Models\Category;
use App\Modules\Shop\Models\Product;
use Database\Seeders\LanguageSeeder;
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

    public function test_home_page_renders_every_block(): void
    {
        $category = Category::factory()->create();
        Product::factory()->count(3)->forCategory($category)->create();

        $response = $this->get('/');

        $response->assertStatus(200);
        foreach (['slides', 'top_products', 'new_products', 'sale_products', 'categories', 'promos', 'reviews', 'faqs'] as $block) {
            $response->assertViewHas($block);
        }
    }

    public function test_hit_tab_holds_only_hits(): void
    {
        $category = Category::factory()->create();
        $hit = Product::factory()->forCategory($category)->create(['is_hit' => true]);
        Product::factory()->forCategory($category)->create(['is_hit' => false]);

        $response = $this->get('/');

        $this->assertSame([$hit->id], $response->viewData('top_products')->pluck('id')->all());
    }

    public function test_sale_block_holds_products_with_a_different_second_price(): void
    {
        $category = Category::factory()->create();
        $discounted = Product::factory()->forCategory($category)->create(['price' => 100, 'price2' => 70]);
        Product::factory()->forCategory($category)->create(['price' => 100, 'price2' => 100]);
        Product::factory()->forCategory($category)->create(['price' => 100, 'price2' => null]);

        $response = $this->get('/');

        $this->assertSame([$discounted->id], $response->viewData('sale_products')->pluck('id')->all());
    }

    public function test_sale_menu_item_shows_while_products_are_on_sale(): void
    {
        $this->seed(LanguageSeeder::class);
        Product::factory()->forCategory(Category::factory()->create())->create(['price' => 100, 'price2' => 70]);

        $this->get('/')->assertSee('class="is-sale"', false);
    }

    public function test_sale_menu_item_is_hidden_without_products_on_sale(): void
    {
        $this->seed(LanguageSeeder::class);
        Product::factory()->forCategory(Category::factory()->create())->create(['price' => 100, 'price2' => 100]);

        $this->get('/')->assertDontSee('class="is-sale"', false);
    }

    public function test_categories_block_skips_categories_without_products(): void
    {
        $withOwn = Category::factory()->create();
        Product::factory()->forCategory($withOwn)->create();

        $withChild = Category::factory()->create();
        $child = Category::factory()->create(['parent_id' => $withChild->id]);
        Product::factory()->forCategory($child)->create();

        $empty = Category::factory()->create();
        Category::factory()->create(['parent_id' => $empty->id]);

        $withHiddenOnly = Category::factory()->create();
        Product::factory()->forCategory($withHiddenOnly)->invisible()->create();

        $ids = $this->get('/')->viewData('categories')->pluck('id')->sort()->values()->all();

        $this->assertSame([$withOwn->id, $withChild->id], $ids);
    }

    public function test_hidden_announces_and_faqs_are_not_shown(): void
    {
        Announce::create(['title' => 'Visible line', 'display' => true]);
        Announce::create(['title' => 'Hidden line', 'display' => false]);
        Faq::create(['title' => 'Hidden question', 'display' => false]);

        $response = $this->get('/');

        $response->assertSee('Visible line');
        $response->assertDontSee('Hidden line');
        $response->assertDontSee('Hidden question');
    }
}
