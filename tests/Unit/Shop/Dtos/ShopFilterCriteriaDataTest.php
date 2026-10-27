<?php

namespace Tests\Unit\Shop\Dtos;

use App\Modules\Shop\Dtos\ShopFilterCriteriaData;
use Tests\TestCase;

class ShopFilterCriteriaDataTest extends TestCase
{
    public function test_it_parses_a_comma_separated_brand_list(): void
    {
        $criteria = ShopFilterCriteriaData::fromQuery('3, 1,2', null, null, null);

        $this->assertSame([3, 1, 2], $criteria->brands);
    }

    public function test_it_parses_brands_passed_as_an_array(): void
    {
        $criteria = ShopFilterCriteriaData::fromQuery(['4', '5'], null, null, null);

        $this->assertSame([4, 5], $criteria->brands);
    }

    public function test_it_drops_non_positive_brand_ids(): void
    {
        $criteria = ShopFilterCriteriaData::fromQuery('0,-2,7,abc', null, null, null);

        $this->assertSame([7], $criteria->brands);
    }

    public function test_parameter_values_are_read_from_the_rest_of_the_query(): void
    {
        $criteria = ShopFilterCriteriaData::fromQuery(null, null, null, null, [
            'season'   => ['3', '4'],
            'material' => '7,abc',
            'size'     => 'abc',
            'brand'    => '1',
            'sort'     => 'newest',
            'page'     => '2',
        ]);

        $this->assertSame(['season' => [3, 4], 'material' => [7]], $criteria->attributes);
    }

    public function test_missing_values_stay_null(): void
    {
        $criteria = ShopFilterCriteriaData::fromQuery(null, null, null, null);

        $this->assertSame([], $criteria->brands);
        $this->assertNull($criteria->priceFrom);
        $this->assertNull($criteria->priceTo);
        $this->assertNull($criteria->search);
    }

    public function test_prices_are_cast_to_float(): void
    {
        $criteria = ShopFilterCriteriaData::fromQuery(null, '10', '99.5', 'sneakers');

        $this->assertSame(10.0, $criteria->priceFrom);
        $this->assertSame(99.5, $criteria->priceTo);
        $this->assertSame('sneakers', $criteria->search);
    }
}
