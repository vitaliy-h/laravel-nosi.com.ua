<?php

namespace Tests\Feature\Filament\Shop;

use App\Filament\Resources\Shop\Brands\Pages\ManageBrands;
use App\Modules\Core\Models\User;
use App\Modules\Shop\Models\Brand;
use App\Modules\Shop\Models\Product;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BrandResourceTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = $this->actingAsAdmin();
        $this->actingAs($this->admin);
    }

    public function test_list_renders_table(): void
    {
        Brand::factory()->count(3)->create();

        Livewire::test(ManageBrands::class)
            ->assertSuccessful();
    }

    public function test_list_can_search_by_title(): void
    {
        $target = Brand::factory()->create(['title' => 'UniqueBrand']);
        $other  = Brand::factory()->create(['title' => 'AnotherBrand']);

        Livewire::test(ManageBrands::class)
            ->searchTable('UniqueBrand')
            ->assertCanSeeTableRecords([$target])
            ->assertCanNotSeeTableRecords([$other]);
    }

    public function test_list_can_search_by_slug(): void
    {
        $target = Brand::factory()->create(['slug' => 'find-this-brand']);
        $other  = Brand::factory()->create(['slug' => 'other-brand']);

        Livewire::test(ManageBrands::class)
            ->searchTable('find-this-brand')
            ->assertCanSeeTableRecords([$target])
            ->assertCanNotSeeTableRecords([$other]);
    }

    public function test_create_brand_with_valid_data(): void
    {
        Livewire::test(ManageBrands::class)
            ->callAction(TestAction::make(CreateAction::getDefaultName())->table(), data: [
                'title'   => 'New Brand',
                'display' => true,
            ])
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('shop_brands', ['title' => 'New Brand']);
    }

    public function test_create_brand_requires_title(): void
    {
        Livewire::test(ManageBrands::class)
            ->callAction(TestAction::make(CreateAction::getDefaultName())->table(), data: [
                'display' => true,
            ])
            ->assertHasFormErrors(['title']);
    }

    public function test_edit_brand_with_valid_data(): void
    {
        $brand = Brand::factory()->create(['title' => 'Old Brand']);

        Livewire::test(ManageBrands::class)
            ->callAction(TestAction::make(EditAction::getDefaultName())->table($brand), data: [
                'title' => 'Updated Brand',
            ])
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('shop_brands', ['id' => $brand->id, 'title' => 'Updated Brand']);
    }

    public function test_delete_brand_without_products(): void
    {
        $brand = Brand::factory()->create();

        Livewire::test(ManageBrands::class)
            ->callAction(TestAction::make(DeleteAction::getDefaultName())->table($brand))
            ->assertSuccessful();

        $this->assertModelMissing($brand);
    }

    public function test_delete_disabled_when_brand_has_products(): void
    {
        $brand = Brand::factory()->create();
        Product::factory()->create(['brand_id' => $brand->id]);

        Livewire::test(ManageBrands::class)
            ->assertActionDisabled(
                TestAction::make(DeleteAction::getDefaultName())->table($brand)
            );

        $this->assertModelExists($brand);
    }
}
