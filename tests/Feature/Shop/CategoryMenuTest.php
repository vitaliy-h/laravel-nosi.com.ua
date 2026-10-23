<?php

namespace Tests\Feature\Shop;

use App\Modules\Page\Models\Page;
use App\Modules\Page\Services\MenuService;
use App\Modules\Shop\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryMenuTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedPagesForRouting();
    }

    public function test_header_menu_lists_marked_categories_before_pages(): void
    {
        Page::factory()->create(['title' => 'Zeta page', 'display_menu' => true]);
        Category::factory()->create(['title' => 'Alpha category', 'display_menu' => true]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSeeInOrder(['Alpha category', 'Zeta page']);
    }

    public function test_header_categories_skip_unmarked_and_hidden_ones(): void
    {
        $marked = Category::factory()->create(['display_menu' => true]);
        Category::factory()->create(['display_menu' => false]);
        Category::factory()->invisible()->create(['display_menu' => true]);

        $ids = app(MenuService::class)->getHeaderCategories()->pluck('id')->all();

        $this->assertSame([$marked->id], $ids);
    }

    public function test_footer_categories_are_the_ones_marked_for_the_footer(): void
    {
        $marked = Category::factory()->create(['display_menu_footer' => true]);
        Category::factory()->create(['display_menu' => true]);

        $ids = app(MenuService::class)->getFooterCategories()->pluck('id')->all();

        $this->assertSame([$marked->id], $ids);
    }
}
