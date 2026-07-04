<?php

namespace Tests\Unit\Shop\Filters;

use App\Modules\Shop\Dtos\Filters\RangeFilterDto;
use App\Modules\Shop\Enums\FilterTypeEnum;
use App\Modules\Shop\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RangeFilterDtoTest extends TestCase
{
    use RefreshDatabase;

    private function make(?float $from, ?float $to, float $min = 0, float $max = 100): RangeFilterDto
    {
        return new RangeFilterDto('price', FilterTypeEnum::RANGE, $min, $max, $from, $to);
    }

    public function test_from_value_and_to_value_fall_back_to_min_max(): void
    {
        $dto = $this->make(null, null);

        $this->assertSame(0.0, $dto->fromValue());
        $this->assertSame(100.0, $dto->toValue());
    }

    public function test_from_value_and_to_value_use_provided_values(): void
    {
        $dto = $this->make(10, 90);

        $this->assertSame(10.0, $dto->fromValue());
        $this->assertSame(90.0, $dto->toValue());
    }

    public function test_slider_values_clamp_within_min_and_max(): void
    {
        $dto = $this->make(-50, 500);

        $this->assertSame(0.0, $dto->fromSlider());
        $this->assertSame(100.0, $dto->toSlider());
    }

    public function test_slider_resets_to_full_range_when_from_exceeds_to(): void
    {
        $dto = $this->make(80, 20);

        $this->assertSame(0.0, $dto->fromSlider());
        $this->assertSame(100.0, $dto->toSlider());
    }

    public function test_apply_filters_products_within_range(): void
    {
        $cheap = Product::factory()->create(['price' => 5]);
        $mid = Product::factory()->create(['price' => 50]);
        $expensive = Product::factory()->create(['price' => 500]);

        $dto = $this->make(10, 100);

        $query = Product::query();
        $dto->apply($query);
        $ids = $query->pluck('id')->all();

        $this->assertNotContains($cheap->id, $ids);
        $this->assertContains($mid->id, $ids);
        $this->assertNotContains($expensive->id, $ids);
    }

    public function test_apply_skips_bounds_when_null(): void
    {
        Product::factory()->count(3)->create();

        $dto = $this->make(null, null);

        $query = Product::query();
        $dto->apply($query);

        $this->assertSame(3, $query->count());
    }

    public function test_with_range_returns_new_instance_with_min_max_replaced(): void
    {
        $dto = $this->make(10, 90, 0, 100);

        $result = $dto->withRange(5, 200);

        $this->assertNotSame($dto, $result);
        $this->assertSame(5.0, $result->min);
        $this->assertSame(200.0, $result->max);
        $this->assertSame($dto->from, $result->from);
    }
}
