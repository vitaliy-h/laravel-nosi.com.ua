<?php

namespace Tests\Feature\Filament\Shop;

use App\Filament\Resources\Shop\Categories\Pages\ManageCategories;
use App\Modules\Shop\Models\Category;
use App\Modules\Shop\Models\Product;
use App\Modules\User\Models\User;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CategoryResourceTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->skipUnlessModules('shop');

        $this->admin = $this->actingAsAdmin();
        $this->actingAs($this->admin);
    }

    public function test_list_renders_table(): void
    {
        Category::factory()->count(3)->create();

        Livewire::test(ManageCategories::class)
            ->assertSuccessful();
    }

    public function test_list_can_search_by_title(): void
    {
        $target = Category::factory()->create(['title' => 'UniqueCategory']);
        $other = Category::factory()->create(['title' => 'AnotherCategory']);

        Livewire::test(ManageCategories::class)
            ->searchTable('UniqueCategory')
            ->assertCanSeeTableRecords([$target])
            ->assertCanNotSeeTableRecords([$other]);
    }

    public function test_list_can_search_by_slug(): void
    {
        $target = Category::factory()->create(['slug' => 'find-this-cat']);
        $other = Category::factory()->create(['slug' => 'other-cat']);

        Livewire::test(ManageCategories::class)
            ->searchTable('find-this-cat')
            ->assertCanSeeTableRecords([$target])
            ->assertCanNotSeeTableRecords([$other]);
    }

    public function test_create_category_with_valid_data(): void
    {
        Livewire::test(ManageCategories::class)
            ->callAction(TestAction::make(CreateAction::getDefaultName())->table(), data: [
                'title'   => 'New Category',
                'display' => true,
            ])
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('shop_categories', ['title' => 'New Category']);
    }

    public function test_create_category_requires_title(): void
    {
        Livewire::test(ManageCategories::class)
            ->callAction(TestAction::make(CreateAction::getDefaultName())->table(), data: [
                'display' => true,
            ])
            ->assertHasFormErrors(['title']);
    }

    public function test_edit_category_with_valid_data(): void
    {
        $category = Category::factory()->create(['title' => 'Old Category']);

        Livewire::test(ManageCategories::class)
            ->callAction(TestAction::make(EditAction::getDefaultName())->table($category), data: [
                'title' => 'Updated Category',
            ])
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('shop_categories', ['id' => $category->id, 'title' => 'Updated Category']);
    }

    public function test_delete_category_without_children_or_products(): void
    {
        $category = Category::factory()->create();

        Livewire::test(ManageCategories::class)
            ->callAction(TestAction::make(DeleteAction::getDefaultName())->table($category))
            ->assertSuccessful();

        $this->assertModelMissing($category);
    }

    public function test_delete_disabled_when_category_has_products(): void
    {
        $category = Category::factory()->create();
        Product::factory()->forCategory($category)->create();

        Livewire::test(ManageCategories::class)
            ->assertActionDisabled(
                TestAction::make(DeleteAction::getDefaultName())->table($category)
            );

        $this->assertModelExists($category);
    }

    public function test_delete_disabled_when_category_has_children(): void
    {
        $parent = Category::factory()->create();
        Category::factory()->create(['parent_id' => $parent->id]);
        $parent->refresh();

        Livewire::test(ManageCategories::class)
            ->assertActionDisabled(
                TestAction::make(DeleteAction::getDefaultName())->table($parent)
            );

        $this->assertModelExists($parent);
    }
}
