<?php

namespace Tests\Feature\Infrastructure;

use App\Modules\Core\Enums\PageModule;
use App\Modules\Core\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageFactoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_page_factory_creates_page_with_custom_slug(): void
    {
        $page = Page::factory()->withSlug('custom')->create();

        $this->assertModelExists($page);
        $this->assertSame('custom', $page->slug);
    }

    public function test_page_factory_creates_index_page_with_empty_slug(): void
    {
        $page = Page::factory()->withSlug('')->forModule(PageModule::INDEX)->create();

        $this->assertModelExists($page);
        $this->assertSame('', $page->slug);
        $this->assertSame(PageModule::INDEX, $page->module);
    }

    public function test_page_factory_invisible_state_sets_display_false(): void
    {
        $page = Page::factory()->invisible()->create();

        $this->assertFalse($page->display);
    }

    public function test_seed_pages_for_routing_creates_expected_route_pages(): void
    {
        $this->seedPagesForRouting();

        $this->assertDatabaseHas('pages', [
            'slug' => '',
            'module' => PageModule::INDEX->value,
        ]);
        $this->assertDatabaseHas('pages', ['slug' => 'news', 'module' => PageModule::NEWS->value]);
        $this->assertDatabaseHas('pages', ['slug' => 'shop', 'module' => PageModule::SHOP->value]);
        $this->assertDatabaseHas('pages', ['slug' => 'auth', 'module' => PageModule::AUTH->value]);
    }
}
