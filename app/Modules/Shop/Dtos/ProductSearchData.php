<?php

namespace App\Modules\Shop\Dtos;

use App\Modules\Shop\Dtos\Filters\ShopFilterData;
use App\Modules\Shop\Enums\SortOptionEnum;
use App\Modules\Shop\Models\Category;

readonly class ProductSearchData
{
    public function __construct(
        public ?Category $category = null,
        public ?ShopFilterData $filter = null,
        public ?SortOptionEnum $sort = null,
        public bool $random = false,
        public ?int $limit = null,
        public bool $paginate = false,
        /** Requested page size; the Query falls back to the default and rejects anything not offered. */
        public ?int $perPage = null,
        public array $with = ['brand', 'category'],
    ) {}
}
