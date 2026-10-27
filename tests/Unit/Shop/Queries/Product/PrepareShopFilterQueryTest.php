<?php

namespace Tests\Unit\Shop\Queries\Product;

use App\Modules\Shop\Dtos\ShopFilterCriteriaData;
use App\Modules\Shop\Models\Attribute;
use App\Modules\Shop\Models\Brand;
use App\Modules\Shop\Models\Category;
use App\Modules\Shop\Models\Product;
use App\Modules\Shop\Queries\Product\BuildProductsQuery;
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
            new ShopFilterCriteriaData(brands: [$brand->id], priceFrom: 50.0, priceTo: 200.0, search: 'shoe'),
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

        $filter = app(PrepareShopFilterQuery::class)->handle($category, new ShopFilterCriteriaData);

        $this->assertSame(100.0, $filter->price->min);
        $this->assertSame(300.0, $filter->price->max);
    }

    public function test_parameter_values_are_counted_without_their_own_selection(): void
    {
        $category = Category::factory()->create();
        $season = Attribute::factory()->withOptions(['Зима', 'Літо', 'Осінь'])->create(['code' => 'season']);
        [$winter, $summer, $autumn] = $season->options->all();

        $warm = Product::factory()->forCategory($category)->create();
        $warm->attributeOptions()->sync([$winter->id]);
        $light = Product::factory()->forCategory($category)->create();
        $light->attributeOptions()->sync([$summer->id]);

        $filter = app(PrepareShopFilterQuery::class)->handle(
            $category,
            new ShopFilterCriteriaData(attributes: ['season' => [$winter->id, 999]]),
        );

        $seasonFilter = $filter->attributes->get('season');
        // an id of no value of the parameter is dropped
        $this->assertSame([$winter->id], $seasonFilter->selected);
        // «Літо» still counts its product while «Зима» is picked; «Осінь» has none
        $this->assertSame([$winter->id => 1, $summer->id => 1], $seasonFilter->items->pluck('products_count', 'id')->all());
        $this->assertNotContains($autumn->id, $seasonFilter->items->pluck('id'));
    }

    public function test_selected_parameter_values_narrow_the_listing(): void
    {
        $category = Category::factory()->create();
        $season = Attribute::factory()->withOptions(['Зима', 'Літо'])->create(['code' => 'season']);
        $material = Attribute::factory()->withOptions(['Вовна', 'Бавовна'])->create(['code' => 'material']);
        [$winter, $summer] = $season->options->all();
        [$wool, $cotton] = $material->options->all();

        $woolWinter = Product::factory()->forCategory($category)->create();
        $woolWinter->attributeOptions()->sync([$winter->id, $wool->id]);
        $cottonSummer = Product::factory()->forCategory($category)->create();
        $cottonSummer->attributeOptions()->sync([$summer->id, $cotton->id]);
        $cottonWinter = Product::factory()->forCategory($category)->create();
        $cottonWinter->attributeOptions()->sync([$winter->id, $cotton->id]);

        $filter = app(PrepareShopFilterQuery::class)->handle(
            $category,
            new ShopFilterCriteriaData(attributes: ['season' => [$winter->id, $summer->id], 'material' => [$cotton->id]]),
        );

        $ids = app(BuildProductsQuery::class)->handle($category, $filter)->pluck('id')->all();

        // either season, and cotton
        $this->assertEqualsCanonicalizing([$cottonSummer->id, $cottonWinter->id], $ids);
    }

    public function test_a_parameter_left_out_of_the_filter_is_not_offered(): void
    {
        Attribute::factory()->withOptions(['X'])->create(['code' => 'hidden', 'is_filterable' => false]);
        Attribute::factory()->withOptions(['Y'])->create(['code' => 'off', 'display' => false]);

        $filter = app(PrepareShopFilterQuery::class)->handle(null, new ShopFilterCriteriaData);

        $this->assertSame([], $filter->attributes->keys()->all());
    }
}
