<?php

namespace Tests\Unit\Shop\ViewModels;

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

    public function test_discount_is_flagged_only_when_a_second_price_is_set(): void
    {
        $plain = ProductViewModel::from(Product::factory()->create(['price' => 100, 'price2' => null]));
        $discounted = ProductViewModel::from(Product::factory()->create(['price' => 100, 'price2' => 80]));

        $this->assertFalse($plain->has_discount);
        $this->assertTrue($discounted->has_discount);
        $this->assertSame(80.0, $discounted->price2);
    }

    public function test_a_product_without_a_picture_still_resolves_an_image(): void
    {
        $vm = ProductViewModel::from(Product::factory()->create(['img' => null]));

        $this->assertFalse($vm->has_image);
        $this->assertNotSame('', $vm->image);
        $this->assertNotSame('', $vm->thumb_url);
    }
}
