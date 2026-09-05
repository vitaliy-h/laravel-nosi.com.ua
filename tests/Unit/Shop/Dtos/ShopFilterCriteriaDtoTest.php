<?php

namespace Tests\Unit\Shop\Dtos;

use App\Modules\Shop\Dtos\ShopFilterCriteriaDto;
use Tests\TestCase;

class ShopFilterCriteriaDtoTest extends TestCase
{
    public function test_it_parses_a_comma_separated_brand_list(): void
    {
        $criteria = ShopFilterCriteriaDto::fromQuery('3, 1,2', null, null, null);

        $this->assertSame([3, 1, 2], $criteria->brands);
    }

    public function test_it_parses_brands_passed_as_an_array(): void
    {
        $criteria = ShopFilterCriteriaDto::fromQuery(['4', '5'], null, null, null);

        $this->assertSame([4, 5], $criteria->brands);
    }

    public function test_it_drops_non_positive_brand_ids(): void
    {
        $criteria = ShopFilterCriteriaDto::fromQuery('0,-2,7,abc', null, null, null);

        $this->assertSame([7], $criteria->brands);
    }

    public function test_missing_values_stay_null(): void
    {
        $criteria = ShopFilterCriteriaDto::fromQuery(null, null, null, null);

        $this->assertSame([], $criteria->brands);
        $this->assertNull($criteria->priceFrom);
        $this->assertNull($criteria->priceTo);
        $this->assertNull($criteria->search);
    }

    public function test_prices_are_cast_to_float(): void
    {
        $criteria = ShopFilterCriteriaDto::fromQuery(null, '10', '99.5', 'sneakers');

        $this->assertSame(10.0, $criteria->priceFrom);
        $this->assertSame(99.5, $criteria->priceTo);
        $this->assertSame('sneakers', $criteria->search);
    }
}
