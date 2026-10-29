<?php

namespace Tests\Feature\Shop;

use App\Modules\Shop\Models\Brand;
use App\Modules\Shop\Models\Category;
use App\Modules\Shop\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShopSeoFieldsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->skipUnlessModules('shop');
    }

    public function test_a_new_category_gets_a_selling_meta_title_and_description(): void
    {
        $category = Category::factory()->create(['title' => 'Жіночі шкарпетки', 'meta_title' => null, 'meta_description' => null]);

        $this->assertStringStartsWith('Жіночі шкарпетки — купити в інтернет-магазині ', $category->meta_title);
        $this->assertStringStartsWith('Купити жіночі шкарпетки в інтернет-магазині ', $category->meta_description);
        $this->assertStringContainsString('доставка Новою поштою', $category->meta_description);
        $this->assertLessThanOrEqual(160, mb_strlen($category->meta_description));
    }

    public function test_a_new_product_description_has_the_brand_and_the_start_of_the_text(): void
    {
        $brand = Brand::factory()->create(['title' => 'DMDBS']);
        $product = Product::factory()->create([
            'title'            => 'Шкарпетки Cashmere',
            'brand_id'         => $brand->id,
            'text'             => '<p>М’які кашемірові шкарпетки з ароматом лаванди, які довго тримають тепло і не скочуються.</p>',
            'meta_title'       => null,
            'meta_description' => null,
        ]);

        $this->assertStringStartsWith('Шкарпетки Cashmere — купити в ', $product->meta_title);
        $this->assertStringStartsWith('Купити шкарпетки Cashmere від DMDBS в інтернет-магазині ', $product->meta_description);
        $this->assertStringContainsString('М’які кашемірові', $product->meta_description);
        $this->assertStringEndsWith('Доставка Новою поштою по Україні, обмін 14 днів.', $product->meta_description);
        $this->assertStringNotContainsString('<p>', $product->meta_description);
        $this->assertLessThanOrEqual(160, mb_strlen($product->meta_description));
    }

    public function test_a_title_starting_with_capitals_keeps_its_case(): void
    {
        $product = Product::factory()->create(['title' => 'DMDBS шкарпетки', 'meta_description' => null]);

        $this->assertStringStartsWith('Купити DMDBS шкарпетки', $product->meta_description);
    }

    public function test_texts_written_by_hand_are_kept(): void
    {
        $category = Category::factory()->create(['meta_title' => 'Свій заголовок', 'meta_description' => 'Свій опис']);

        $this->assertSame('Свій заголовок', $category->meta_title);
        $this->assertSame('Свій опис', $category->meta_description);
    }

    public function test_generated_slug_uses_ukrainian_transliteration(): void
    {
        $category = Category::factory()->create(['title' => 'Чоловічі шкарпетки на хутрі', 'slug' => null]);

        $this->assertSame('cholovichi-shkarpetky-na-khutri', $category->slug);
    }

    public function test_the_category_page_falls_back_to_the_rule_for_old_records(): void
    {
        $this->seedPagesForRouting();
        $category = Category::factory()->create(['title' => 'Колготки']);
        Category::whereKey($category->id)->update(['meta_title' => null, 'meta_description' => null]);

        $response = $this->get('/shop/' . $category->slug);

        $response->assertSee('<title>Колготки — купити в інтернет-магазині ', false);
        $response->assertSee('<meta name="description" content="Купити колготки в інтернет-магазині ', false);
    }
}
