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

    public function test_header_category_brings_its_visible_children_as_a_dropdown(): void
    {
        $women = Category::factory()->create(['title' => 'Women', 'display_menu' => true]);
        Category::factory()->create(['title' => 'Women socks', 'parent_id' => $women->id]);
        Category::factory()->invisible()->create(['title' => 'Hidden child', 'parent_id' => $women->id]);

        $categories = app(MenuService::class)->getHeaderCategories();

        $this->assertSame(['Women socks'], $categories->first()->children->pluck('title')->all());

        $response = $this->get('/');
        $response->assertSeeInOrder(['Women', 'Women socks']);
        $response->assertDontSee('Hidden child');
    }

    public function test_marked_category_inside_a_marked_parent_shows_only_in_the_dropdown(): void
    {
        $women = Category::factory()->create(['display_menu' => true]);
        $socks = Category::factory()->create(['parent_id' => $women->id, 'display_menu' => true]);

        $categories = app(MenuService::class)->getHeaderCategories();

        $this->assertSame([$women->id], $categories->pluck('id')->all());
        $this->assertSame([$socks->id], $categories->first()->children->pluck('id')->all());
    }

    public function test_footer_categories_are_the_ones_marked_for_the_footer(): void
    {
        $marked = Category::factory()->create(['display_menu_footer' => true]);
        Category::factory()->create(['display_menu' => true]);

        $ids = app(MenuService::class)->getFooterCategories()->pluck('id')->all();

        $this->assertSame([$marked->id], $ids);
    }
}
