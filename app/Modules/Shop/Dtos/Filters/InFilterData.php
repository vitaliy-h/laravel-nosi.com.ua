<?php

namespace App\Modules\Shop\Dtos\Filters;

use App\Modules\Shop\Enums\FilterTypeEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class InFilterData extends BaseFilterData
{
    public function __construct(
        public readonly string $key,
        public readonly FilterTypeEnum $type,
        public readonly Collection $items,
        public readonly array $selected,
    ) {}

    public function withItems(Collection $items): self
    {
        return new self($this->key, $this->type, $items, $this->selected);
    }

    public function apply(Builder $query): void
    {
        if (empty($this->selected)) {
            return;
        }

        $column = $this->key === 'brand' ? 'brand_id' : $this->key;

        $query->whereIn($column, $this->selected);
    }
}
