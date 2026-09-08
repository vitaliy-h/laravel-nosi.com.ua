<?php

namespace App\Modules\Shop\Dtos\Filters;

use App\Modules\Shop\Enums\FilterTypeEnum;
use Illuminate\Database\Eloquent\Builder;

class SearchFilterData extends BaseFilterData
{
    public function __construct(
        public readonly string $key,
        public readonly FilterTypeEnum $type,
        public readonly ?string $value,
    ) {}

    public function apply(Builder $query): void
    {
        if ($this->value !== null && trim($this->value) !== '') {
            $query->where('title', 'like', '%' . trim($this->value) . '%');
        }
    }
}
