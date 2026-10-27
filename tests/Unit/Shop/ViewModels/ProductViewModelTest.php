<?php

namespace Tests\Unit\Shop\ViewModels;

use App\Modules\Shop\Models\Attribute;
use App\Modules\Shop\Models\Category;
use App\Modules\Shop\Models\Product;
use App\Modules\Shop\ViewModels\ProductViewModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductViewModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_builds_the_product_url_from_the_loaded_category(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->forCategory($category)->create();

        $vm = ProductViewModel::from($product->load('category'));

        $this->assertSame(
            save_route('shop.product', [$category->slug, $product->slug_with_id]),
            $vm->url
        );
        $this->assertSame($category->id, $vm->category->id);
    }

    /**
     * A missing category costs the link, not a query per card.
     */
    public function test_it_has_no_url_when_the_category_is_not_loaded(): void
    {
        $product = Product::factory()->create();

        $vm = ProductViewModel::from($product);

        $this->assertNull($vm->url);
        $this->assertNull($vm->category);
        $this->assertNull($vm->root_category_id);
    }

    public function test_root_category_id_is_the_category_itself_when_it_is_a_root(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->forCategory($category)->create();

        $vm = ProductViewModel::from($product->load('category'));

        $this->assertSame($category->id, $vm->root_category_id);
    }

    public function test_root_category_id_walks_up_to_the_top_ancestor(): void
    {
        $root = Category::factory()->create();
        $child = Category::factory()->create();
        $child->appendToNode($root)->save();

        $product = Product::factory()->forCategory($child)->create();

        $vm = ProductViewModel::from($product->load('category.ancestors'));

        $this->assertSame($root->id, $vm->root_category_id);
    }

    public function test_root_category_id_is_null_for_a_nested_category_without_loaded_ancestors(): void
    {
        $root = Category::factory()->create();
        $child = Category::factory()->create();
        $child->appendToNode($root)->save();

        $product = Product::factory()->forCategory($child)->create();

        $vm = ProductViewModel::from($product->load('category'));

        $this->assertNull($vm->root_category_id);
    }

    public function test_packs_are_listed_smallest_first_with_the_price_of_the_smallest(): void
    {
        $vm = ProductViewModel::from(Product::factory()->create([
            'packs' => [['quantity' => 10, 'price' => 700], ['quantity' => 1, 'price' => 89], ['quantity' => 5, 'price' => 399]],
        ]));

        $this->assertSame([1, 5, 10], array_column($vm->packs, 'quantity'));
        $this->assertSame(89.0, $vm->price);
        $this->assertStringStartsWith('5 ', $vm->packs[1]['label']);
    }

    public function test_the_unit_switches_to_packs_for_a_product_sold_in_packs(): void
    {
        $pieces = ProductViewModel::from(Product::factory()->create(['is_pack' => false]));
        $packs = ProductViewModel::from(Product::factory()->create(['is_pack' => true]));

        $this->assertSame(ProductViewModel::unit(false), $pieces->unit);
        $this->assertSame(ProductViewModel::unit(true), $packs->unit);
        $this->assertNotSame($pieces->unit, $packs->unit);
    }

    public function test_specs_group_the_chosen_values_by_parameter(): void
    {
        $season = Attribute::factory()->withOptions(['Зима', 'Літо', 'Демісезон'])->create(['title' => 'Сезон']);
        $hidden = Attribute::factory()->withOptions(['X'])->create(['display' => false]);
        $product = Product::factory()->create();
        $product->attributeOptions()->sync([
            ...$season->options->whereIn('title', ['Зима', 'Демісезон'])->pluck('id'),
            ...$hidden->options->pluck('id'),
        ]);

        $vm = ProductViewModel::from($product->load('attributeOptions.attribute'));

        $this->assertSame([['title' => 'Сезон', 'values' => 'Зима, Демісезон']], $vm->specs);
    }

    public function test_a_product_without_a_picture_still_resolves_an_image(): void
    {
        $vm = ProductViewModel::from(Product::factory()->create(['img' => null]));

        $this->assertFalse($vm->has_image);
        $this->assertNotSame('', $vm->image);
        $this->assertNotSame('', $vm->thumb_url);
    }
}
