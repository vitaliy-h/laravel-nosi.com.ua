<?php

namespace App\Modules\Shop\Dtos\Filters;

use App\Modules\Shop\Enums\FilterTypeEnum;
use Illuminate\Database\Eloquent\Builder;

class SortFilterDto extends BaseFilterDto
{
    public function __construct(
        public readonly string $key,
        public readonly FilterTypeEnum $type,
        public readonly ?string $value,
    ) {
    }

    /**
     * Sorting is applied separately in ShopRepository::applySorting, not as a query filter.
     */
    public function apply(Builder $query): void
    {
    }
}
