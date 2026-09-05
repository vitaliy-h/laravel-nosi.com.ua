<?php

namespace Tests\Feature\Sitemap;

use App\Modules\News\Models\News;
use App\Modules\Page\Enums\PageModule;
use App\Modules\Page\Models\Page;
use App\Modules\Shop\Models\Category;
use App\Modules\Shop\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SitemapTest extends TestCase
{
    use RefreshDatabase;

    private function makeModulePage(PageModule $module, bool $display = true, bool $indexable = true): Page
    {
        return Page::factory()->forModule($module)->create([
            'slug'         => $module->value,
            'display'      => $display,
            'is_indexable' => $indexable,
        ]);
    }

    private function skipIfNewsDisabled(): void
    {
        $this->skipUnlessModules('news');
    }

    private function skipIfShopDisabled(): void
    {
        $this->skipUnlessModules('shop');
    }

    // -------------------------------------------------------------------------
    // Sitemap index
    // -------------------------------------------------------------------------

    public function test_sitemap_index_returns_200_with_xml_content_type(): void
    {
        $response = $this->get('/sitemap.xml');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/xml');
    }

    public function test_sitemap_index_always_contains_base_sitemap_link(): void
    {
        $response = $this->get('/sitemap.xml');

        $response->assertSee('sitemap-base.xml', false);
    }

    public function test_sitemap_index_includes_news_links_when_news_module_is_indexable(): void
    {
        $this->skipIfNewsDisabled();

        $this->makeModulePage(PageModule::NEWS);
        News::factory()->create(['slug' => 'article-one']);

        $response = $this->get('/sitemap.xml');

        $response->assertSee('sitemap-news-1.xml', false);
    }

    public function test_sitemap_index_omits_news_links_when_news_module_display_is_false(): void
    {
        $this->skipIfNewsDisabled();

        $this->makeModulePage(PageModule::NEWS, display: false, indexable: true);
        News::factory()->create(['slug' => 'article-one']);

        $response = $this->get('/sitemap.xml');

        $response->assertDontSee('sitemap-news-', false);
    }

    public function test_sitemap_index_omits_news_links_when_news_module_not_indexable(): void
    {
        $this->skipIfNewsDisabled();

        $this->makeModulePage(PageModule::NEWS, display: true, indexable: false);
        News::factory()->create(['slug' => 'article-one']);

        $response = $this->get('/sitemap.xml');

        $response->assertDontSee('sitemap-news-', false);
    }

    public function test_sitemap_index_includes_shop_links_when_shop_module_is_indexable(): void
    {
        $this->skipIfShopDisabled();

        $this->makeModulePage(PageModule::SHOP);
        $category = Category::factory()->create();
        Product::factory()->forCategory($category)->create();

        $response = $this->get('/sitemap.xml');

        $response->assertSee('sitemap-categories-1.xml', false);
        $response->assertSee('sitemap-products-1.xml', false);
    }

    public function test_sitemap_index_omits_shop_links_when_shop_module_not_indexable(): void
    {
        $this->skipIfShopDisabled();

        $this->makeModulePage(PageModule::SHOP, display: true, indexable: false);
        $category = Category::factory()->create();
        Product::factory()->forCategory($category)->create();

        $response = $this->get('/sitemap.xml');

        $response->assertDontSee('sitemap-categories-', false);
        $response->assertDontSee('sitemap-products-', false);
    }

    public function test_sitemap_index_omits_shop_links_when_shop_module_display_is_false(): void
    {
        $this->skipIfShopDisabled();

        $this->makeModulePage(PageModule::SHOP, display: false, indexable: true);

        $response = $this->get('/sitemap.xml');

        $response->assertDontSee('sitemap-categories-', false);
        $response->assertDontSee('sitemap-products-', false);
    }

    // -------------------------------------------------------------------------
    // Base sitemap
    // -------------------------------------------------------------------------

    public function test_sitemap_base_returns_200_with_xml_content_type(): void
    {
        $response = $this->get('/sitemap-base.xml');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/xml');
    }

    public function test_sitemap_base_includes_indexable_visible_pages(): void
    {
        Page::factory()->forModule(PageModule::TEXT)->create([
            'slug'         => 'about-us',
            'display'      => true,
            'is_indexable' => true,
        ]);

        $response = $this->get('/sitemap-base.xml');

        $response->assertSee('about-us', false);
    }

    public function test_sitemap_base_excludes_non_indexable_pages(): void
    {
        Page::factory()->create([
            'slug'         => 'private-page',
            'display'      => true,
            'is_indexable' => false,
        ]);

        $response = $this->get('/sitemap-base.xml');

        $response->assertDontSee('private-page', false);
    }

    public function test_sitemap_base_excludes_invisible_pages(): void
    {
        Page::factory()->invisible()->create([
            'slug'         => 'invisible-page',
            'is_indexable' => true,
        ]);

        $response = $this->get('/sitemap-base.xml');

        $response->assertDontSee('invisible-page', false);
    }

    // -------------------------------------------------------------------------
    // News sitemap
    // -------------------------------------------------------------------------

    public function test_sitemap_news_returns_200_with_xml_content_type(): void
    {
        $response = $this->get('/sitemap-news-1.xml');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/xml');
    }

    public function test_sitemap_news_includes_visible_items_when_module_is_indexable(): void
    {
        $this->skipIfNewsDisabled();

        $this->makeModulePage(PageModule::NEWS);
        News::factory()->create(['slug' => 'breaking-news']);

        $response = $this->get('/sitemap-news-1.xml');

        $response->assertSee('breaking-news', false);
    }

    public function test_sitemap_news_is_empty_when_module_not_indexable(): void
    {
        $this->skipIfNewsDisabled();

        $this->makeModulePage(PageModule::NEWS, display: true, indexable: false);
        News::factory()->create(['slug' => 'breaking-news']);

        $response = $this->get('/sitemap-news-1.xml');

        $response->assertDontSee('breaking-news', false);
    }

    public function test_sitemap_news_excludes_invisible_news(): void
    {
        $this->skipIfNewsDisabled();

        $this->makeModulePage(PageModule::NEWS);
        News::factory()->invisible()->create(['slug' => 'hidden-article']);

        $response = $this->get('/sitemap-news-1.xml');

        $response->assertDontSee('hidden-article', false);
    }

    public function test_sitemap_news_page_zero_returns_404(): void
    {
        $response = $this->get('/sitemap-news-0.xml');

        $response->assertStatus(404);
    }

    // -------------------------------------------------------------------------
    // Categories sitemap
    // -------------------------------------------------------------------------

    public function test_sitemap_categories_returns_200_with_xml_content_type(): void
    {
        $response = $this->get('/sitemap-categories-1.xml');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/xml');
    }

    public function test_sitemap_categories_includes_visible_categories_when_shop_is_indexable(): void
    {
        $this->skipIfShopDisabled();

        $this->makeModulePage(PageModule::SHOP);
        Category::factory()->create(['slug' => 'smartphones']);

        $response = $this->get('/sitemap-categories-1.xml');

        $response->assertSee('smartphones', false);
    }

    public function test_sitemap_categories_is_empty_when_shop_not_indexable(): void
    {
        $this->skipIfShopDisabled();

        $this->makeModulePage(PageModule::SHOP, display: true, indexable: false);
        Category::factory()->create(['slug' => 'smartphones']);

        $response = $this->get('/sitemap-categories-1.xml');

        $response->assertDontSee('smartphones', false);
    }

    public function test_sitemap_categories_excludes_invisible_categories(): void
    {
        $this->skipIfShopDisabled();

        $this->makeModulePage(PageModule::SHOP);
        Category::factory()->invisible()->create(['slug' => 'hidden-cat']);

        $response = $this->get('/sitemap-categories-1.xml');

        $response->assertDontSee('hidden-cat', false);
    }

    public function test_sitemap_categories_page_zero_returns_404(): void
    {
        $response = $this->get('/sitemap-categories-0.xml');

        $response->assertStatus(404);
    }

    // -------------------------------------------------------------------------
    // Products sitemap
    // -------------------------------------------------------------------------

    public function test_sitemap_products_returns_200_with_xml_content_type(): void
    {
        $response = $this->get('/sitemap-products-1.xml');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/xml');
    }

    public function test_sitemap_products_includes_visible_products_when_shop_is_indexable(): void
    {
        $this->skipIfShopDisabled();

        $this->makeModulePage(PageModule::SHOP);
        $category = Category::factory()->create(['slug' => 'phones']);
        $product = Product::factory()->forCategory($category)->create(['slug' => 'iphone']);

        $response = $this->get('/sitemap-products-1.xml');

        $response->assertSee('phones', false);
        $response->assertSee('iphone-' . $product->id, false);
    }

    public function test_sitemap_products_is_empty_when_shop_not_indexable(): void
    {
        $this->skipIfShopDisabled();

        $this->makeModulePage(PageModule::SHOP, display: true, indexable: false);
        $category = Category::factory()->create(['slug' => 'phones']);
        Product::factory()->forCategory($category)->create(['slug' => 'iphone']);

        $response = $this->get('/sitemap-products-1.xml');

        $response->assertDontSee('iphone', false);
    }

    public function test_sitemap_products_excludes_invisible_products(): void
    {
        $this->skipIfShopDisabled();

        $this->makeModulePage(PageModule::SHOP);
        $category = Category::factory()->create();
        Product::factory()->forCategory($category)->invisible()->create(['slug' => 'ghost-product']);

        $response = $this->get('/sitemap-products-1.xml');

        $response->assertDontSee('ghost-product', false);
    }

    public function test_sitemap_products_page_zero_returns_404(): void
    {
        $response = $this->get('/sitemap-products-0.xml');

        $response->assertStatus(404);
    }
}
