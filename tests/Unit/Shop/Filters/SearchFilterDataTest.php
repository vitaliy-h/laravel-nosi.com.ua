<?php

namespace Tests\Unit\Shop\Filters;

use App\Modules\Shop\Dtos\Filters\SearchFilterData;
use App\Modules\Shop\Enums\FilterTypeEnum;
use App\Modules\Shop\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchFilterDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_apply_filters_products_by_partial_title_match(): void
    {
        $matching = Product::factory()->create(['title' => 'Red Widget']);
        $other = Product::factory()->create(['title' => 'Blue Gadget']);

        $dto = new SearchFilterData('search', FilterTypeEnum::SEARCH, 'widget');

        $query = Product::query();
        $dto->apply($query);
        $ids = $query->pluck('id')->all();

        $this->assertContains($matching->id, $ids);
        $this->assertNotContains($other->id, $ids);
    }

    public function test_apply_ignores_null_value(): void
    {
        Product::factory()->count(2)->create();

        $dto = new SearchFilterData('search', FilterTypeEnum::SEARCH, null);

        $query = Product::query();
        $dto->apply($query);

        $this->assertSame(2, $query->count());
    }

    public function test_apply_ignores_blank_value(): void
    {
        Product::factory()->count(2)->create();

        $dto = new SearchFilterData('search', FilterTypeEnum::SEARCH, '   ');

        $query = Product::query();
        $dto->apply($query);

        $this->assertSame(2, $query->count());
    }
}
