<?php

namespace Tests\Unit\Shop\ViewModels;

use App\Modules\Shop\Models\Category;
use App\Modules\Shop\Queries\Category\GetAllCategoriesQuery;
use App\Modules\Shop\ViewModels\CategoryViewModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryViewModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_prepares_the_category_url(): void
    {
        $category = Category::factory()->create();

        $vm = CategoryViewModel::from($category);

        $this->assertSame(save_route('shop.category', $category->slug), $vm->url);
    }

    /**
     * Outside the sidebar tree nothing marks a category as active or counts its
     * products, and the ViewModel must not invent either.
     */
    public function test_a_standalone_category_is_neither_active_nor_opened(): void
    {
        $vm = CategoryViewModel::from(Category::factory()->create());

        $this->assertFalse($vm->is_active);
        $this->assertFalse($vm->is_opened);
        $this->assertSame(0, $vm->products_count);
        $this->assertTrue($vm->children->isEmpty());
    }

    public function test_it_carries_the_tree_flags_and_children_of_the_sidebar_query(): void
    {
        $root = Category::factory()->create();
        $child = Category::factory()->create();
        $child->appendToNode($root)->save();

        $tree = app(GetAllCategoriesQuery::class)->handle($child->id);

        $rootView = CategoryViewModel::from($tree->firstWhere('id', $root->id));

        $this->assertTrue($rootView->is_opened, 'the ancestor of the active category is opened');
        $this->assertFalse($rootView->is_active);

        $childView = $rootView->children->first();

        $this->assertNotNull($childView);
        $this->assertSame($child->id, $childView->id);
        $this->assertTrue($childView->is_active);
    }
}
