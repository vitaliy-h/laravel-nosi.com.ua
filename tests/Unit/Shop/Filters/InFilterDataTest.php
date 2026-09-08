<?php

namespace Tests\Unit\Shop\Filters;

use App\Modules\Shop\Dtos\Filters\InFilterData;
use App\Modules\Shop\Enums\FilterTypeEnum;
use App\Modules\Shop\Models\Brand;
use App\Modules\Shop\Models\Category;
use App\Modules\Shop\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InFilterDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_apply_filters_by_brand_id_when_key_is_brand(): void
    {
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();
        $otherBrand = Brand::factory()->create();
        $matching = Product::factory()->forCategory($category)->create(['brand_id' => $brand->id]);
        $other = Product::factory()->forCategory($category)->create(['brand_id' => $otherBrand->id]);

        $dto = new InFilterData('brand', FilterTypeEnum::IN, collect(), [$brand->id]);

        $query = Product::query();
        $dto->apply($query);
        $ids = $query->pluck('id')->all();

        $this->assertContains($matching->id, $ids);
        $this->assertNotContains($other->id, $ids);
    }

    public function test_apply_does_nothing_when_no_selection(): void
    {
        Product::factory()->count(2)->create();

        $dto = new InFilterData('brand', FilterTypeEnum::IN, collect(), []);

        $query = Product::query();
        $dto->apply($query);

        $this->assertSame(2, $query->count());
    }

    public function test_with_items_returns_new_instance_with_items_replaced(): void
    {
        $dto = new InFilterData('brand', FilterTypeEnum::IN, collect(), [1]);
        $items = collect(['a', 'b']);

        $result = $dto->withItems($items);

        $this->assertNotSame($dto, $result);
        $this->assertSame($items, $result->items);
        $this->assertSame($dto->selected, $result->selected);
    }
}
