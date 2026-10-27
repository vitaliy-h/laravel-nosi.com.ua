<?php

namespace App\Modules\Shop\Dtos\Filters;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;

class ShopFilterData extends Data
{
    /**
     * @param  Collection<string, AttributeFilterData>|null  $attributes  keyed by the parameter's code
     */
    public function __construct(
        public readonly InFilterData $brand,
        public readonly RangeFilterData $price,
        public readonly SearchFilterData $search,
        public readonly ?Collection $attributes = null,
    ) {}

    public function withBrand(InFilterData $brand): self
    {
        return new self($brand, $this->price, $this->search, $this->attributes);
    }

    public function withPrice(RangeFilterData $price): self
    {
        return new self($this->brand, $price, $this->search, $this->attributes);
    }

    /**
     * @param  Collection<string, AttributeFilterData>  $attributes
     */
    public function withAttributes(Collection $attributes): self
    {
        return new self($this->brand, $this->price, $this->search, $attributes);
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
        ])->merge($this->attributes ?? collect());
    }
}
