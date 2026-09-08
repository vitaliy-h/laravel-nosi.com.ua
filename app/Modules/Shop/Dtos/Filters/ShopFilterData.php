<?php

namespace App\Modules\Shop\Dtos\Filters;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;

class ShopFilterData extends Data
{
    public function __construct(
        public readonly InFilterData $brand,
        public readonly RangeFilterData $price,
        public readonly SearchFilterData $search,
    ) {}

    public function withBrand(InFilterData $brand): self
    {
        return new self($brand, $this->price, $this->search);
    }

    public function withPrice(RangeFilterData $price): self
    {
        return new self($this->brand, $price, $this->search);
    }

    /**
     * @return Collection<string, BaseFilterData>
     */
    public function toApplicable(): Collection
    {
        return collect([
            'brand'  => $this->brand,
            'price'  => $this->price,
            'search' => $this->search,
        ]);
    }
}
