<?php

namespace App\Modules\Shop\Dtos\Filters;

use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;

class ShopFilterDto extends Data
{
    public function __construct(
        public readonly InFilterDto $brand,
        public readonly RangeFilterDto $price,
        public readonly SearchFilterDto $search,
    ) {
    }

    public function withBrand(InFilterDto $brand): self
    {
        return new self($brand, $this->price, $this->search);
    }

    public function withPrice(RangeFilterDto $price): self
    {
        return new self($this->brand, $price, $this->search);
    }

    /**
     * @return Collection<string, BaseFilterDto>
     */
    public function toApplicable(): Collection
    {
        return collect([
            'brand' => $this->brand,
            'price' => $this->price,
            'search' => $this->search,
        ]);
    }
}
