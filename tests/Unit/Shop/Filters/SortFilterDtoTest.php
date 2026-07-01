<?php

namespace Tests\Unit\Shop\Filters;

use App\Modules\Shop\Dtos\Filters\SortFilterDto;
use App\Modules\Shop\Enums\FilterTypeEnum;
use App\Modules\Shop\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SortFilterDtoTest extends TestCase
{
    use RefreshDatabase;

    public function test_apply_does_not_modify_the_query(): void
    {
        Product::factory()->count(2)->create();

        $dto = new SortFilterDto('sort', FilterTypeEnum::SORT, 'price_asc');

        $query = Product::query();
        $dto->apply($query);

        $this->assertSame(2, $query->count());
    }
}
