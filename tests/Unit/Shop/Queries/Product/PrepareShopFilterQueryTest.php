<?php

namespace Tests\Unit\Shop\Queries\Product;

use App\Modules\Shop\Dtos\ShopFilterCriteriaDto;
use App\Modules\Shop\Models\Brand;
use App\Modules\Shop\Models\Category;
use App\Modules\Shop\Models\Product;
use App\Modules\Shop\Queries\Product\PrepareShopFilterQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The Query takes a DTO, so it runs with no HTTP request in play at all.
 */
class PrepareShopFilterQueryTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_builds_a_filter_without_a_request(): void
    {
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();
        Product::factory()->forCategory($category)->create(['brand_id' => $brand->id, 'price' => 100]);
        Product::factory()->forCategory($category)->create(['brand_id' => $brand->id, 'price' => 300]);

        $filter = app(PrepareShopFilterQuery::class)->handle(
            $category,
            new ShopFilterCriteriaDto(brands: [$brand->id], priceFrom: 50.0, priceTo: 200.0, search: 'shoe'),
        );

        $this->assertSame([$brand->id], $filter->brand->selected);
        $this->assertSame(50.0, $filter->price->from);
        $this->assertSame(200.0, $filter->price->to);
        $this->assertSame('shoe', $filter->search->value);
    }

    public function test_the_price_range_comes_from_the_matching_products(): void
    {
        $category = Category::factory()->create();
        Product::factory()->forCategory($category)->create(['price' => 100]);
        Product::factory()->forCategory($category)->create(['price' => 300]);

        $filter = app(PrepareShopFilterQuery::class)->handle($category, new ShopFilterCriteriaDto);

        $this->assertSame(100.0, $filter->price->min);
        $this->assertSame(300.0, $filter->price->max);
    }
}
