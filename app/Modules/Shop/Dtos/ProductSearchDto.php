<?php

namespace App\Modules\Shop\Dtos;

use App\Modules\Shop\Dtos\Filters\ShopFilterDto;
use App\Modules\Shop\Enums\SortOptionEnum;
use App\Modules\Shop\Models\Category;

readonly class ProductSearchDto
{
    public function __construct(
        public ?Category $category = null,
        public ?ShopFilterDto $filter = null,
        public ?SortOptionEnum $sort = null,
        public bool $random = false,
        public ?int $limit = null,
        public bool $paginate = false,
        public array $with = ['brand', 'category'],
    ) {}
}
