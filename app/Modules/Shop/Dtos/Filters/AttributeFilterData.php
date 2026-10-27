<?php

namespace App\Modules\Shop\Dtos\Filters;

use App\Modules\Shop\Enums\FilterTypeEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * A product parameter in the catalogue sidebar (size, season, material…).
 * `key` is the parameter's `code` — its name in the URL (`?season[]=3`).
 * Values of one parameter are alternatives (any of them), different
 * parameters narrow each other down.
 */
class AttributeFilterData extends BaseFilterData
{
    /**
     * @param  Collection<int, object{id: int, title: string, products_count: int}>  $items
     * @param  array<int, int>  $selected  option ids
     */
    public function __construct(
        public readonly string $key,
        public readonly FilterTypeEnum $type,
        public readonly int $attributeId,
        public readonly string $title,
        public readonly Collection $items,
        public readonly array $selected,
    ) {}

    public function withItems(Collection $items): self
    {
        return new self($this->key, $this->type, $this->attributeId, $this->title, $items, $this->selected);
    }

    public function apply(Builder $query): void
    {
        if (empty($this->selected)) {
            return;
        }

        $query->whereExists(fn ($sub) => $sub
            ->from('shop_attribute_option_product')
            ->whereColumn('shop_attribute_option_product.product_id', 'shop_products.id')
            ->whereIn('shop_attribute_option_product.option_id', $this->selected));
    }
}
