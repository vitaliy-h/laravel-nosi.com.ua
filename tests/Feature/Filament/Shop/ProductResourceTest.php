<?php

namespace Tests\Feature\Filament\Shop;

use App\Filament\Resources\Shop\Products\Pages\ManageProducts;
use App\Filament\Resources\Shop\ProductReviews\ProductReviewResource;
use App\Modules\Core\Models\User;
use App\Modules\Core\Support\ModuleGate;
use App\Modules\Shop\Models\Brand;
use App\Modules\Shop\Models\Category;
use App\Modules\Shop\Models\Product;
use App\Modules\Shop\Models\ProductReview;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\Testing\TestAction;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProductResourceTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        if (! config('modules.shop')) {
            $this->markTestSkipped('Shop module is disabled');
        }

        $this->admin = $this->actingAsAdmin();
        $this->actingAs($this->admin);
    }

    public function test_list_renders_table(): void
    {
        Product::factory()->count(3)->create();

        Livewire::test(ManageProducts::class)
            ->assertSuccessful();
    }

    public function test_list_can_search_by_title(): void
    {
        $target = Product::factory()->create(['title' => 'UniqueProduct']);
        $other = Product::factory()->create(['title' => 'AnotherProduct']);

        Livewire::test(ManageProducts::class)
            ->searchTable('UniqueProduct')
            ->assertCanSeeTableRecords([$target])
            ->assertCanNotSeeTableRecords([$other]);
    }

    public function test_list_can_search_by_article(): void
    {
        $target = Product::factory()->create(['article' => 'ART-UNIQUE-99']);
        $other = Product::factory()->create(['article' => 'ART-OTHER-00']);

        Livewire::test(ManageProducts::class)
            ->searchTable('ART-UNIQUE-99')
            ->assertCanSeeTableRecords([$target])
            ->assertCanNotSeeTableRecords([$other]);
    }

    public function test_list_can_filter_by_category(): void
    {
        $cat1 = Category::factory()->create();
        $cat2 = Category::factory()->create();

        $inCat = Product::factory()->forCategory($cat1)->create();
        $outCat = Product::factory()->forCategory($cat2)->create();

        Livewire::test(ManageProducts::class)
            ->filterTable('category_id', $cat1->id)
            ->assertCanSeeTableRecords([$inCat])
            ->assertCanNotSeeTableRecords([$outCat]);
    }

    public function test_list_can_filter_by_brand(): void
    {
        // The brand filter is part of the shop_brands module and is hidden
        // from the table when that module is switched off.
        if (! ModuleGate::isEnabled('shop_brands')) {
            $this->markTestSkipped('Module [shop_brands] is disabled.');
        }

        $brand1 = Brand::factory()->create();
        $brand2 = Brand::factory()->create();

        $inBrand = Product::factory()->create(['brand_id' => $brand1->id]);
        $outBrand = Product::factory()->create(['brand_id' => $brand2->id]);

        Livewire::test(ManageProducts::class)
            ->filterTable('brand_id', $brand1)
            ->assertCanSeeTableRecords([$inBrand])
            ->assertCanNotSeeTableRecords([$outBrand]);
    }

    public function test_create_product_with_valid_data(): void
    {
        $category = Category::factory()->create();

        Livewire::test(ManageProducts::class)
            ->callAction(TestAction::make(CreateAction::getDefaultName())->table(), data: [
                'title' => 'New Product',
                'category_id' => $category->id,
                'display' => true,
            ])
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('shop_products', ['title' => 'New Product']);
    }

    public function test_create_product_requires_title(): void
    {
        $category = Category::factory()->create();

        Livewire::test(ManageProducts::class)
            ->callAction(TestAction::make(CreateAction::getDefaultName())->table(), data: [
                'category_id' => $category->id,
            ])
            ->assertHasFormErrors(['title']);
    }

    public function test_create_product_requires_category(): void
    {
        Livewire::test(ManageProducts::class)
            ->callAction(TestAction::make(CreateAction::getDefaultName())->table(), data: [
                'title' => 'No Category',
                'display' => true,
            ])
            ->assertHasFormErrors(['category_id']);
    }

    public function test_edit_product_with_valid_data(): void
    {
        $product = Product::factory()->create(['title' => 'Old Product']);

        Livewire::test(ManageProducts::class)
            ->callAction(TestAction::make(EditAction::getDefaultName())->table($product), data: [
                'title' => 'Updated Product',
                'category_id' => $product->category_id,
            ])
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('shop_products', ['id' => $product->id, 'title' => 'Updated Product']);
    }

    public function test_rating_column_shows_aggregates_and_links_to_filtered_reviews(): void
    {
        // The rating column links into ReviewResource and is hidden with the
        // shop_reviews module, whose resource then has no route to link to.
        if (! ModuleGate::isEnabled('shop_reviews')) {
            $this->markTestSkipped('Module [shop_reviews] is disabled.');
        }

        $product = Product::factory()->create();

        ProductReview::factory()->count(2)->forProduct($product)->withRating(4)->create();

        $product->refresh();

        Livewire::test(ManageProducts::class)
            ->assertTableColumnStateSet('rating', 4.0, $product)
            ->assertTableColumnFormattedStateSet('rating', '4.00 (2)', $product)
            ->assertTableColumnExists(
                'rating',
                fn (TextColumn $column): bool => $column->getUrl() === ProductReviewResource::getUrl(parameters: [
                    'filters' => ['product_id' => ['value' => $product->id]],
                ]),
                $product,
            );
    }

    public function test_delete_product(): void
    {
        $product = Product::factory()->create();

        Livewire::test(ManageProducts::class)
            ->callAction(TestAction::make(DeleteAction::getDefaultName())->table($product))
            ->assertSuccessful();

        $this->assertModelMissing($product);
    }
}
