<?php

namespace App\Modules\Shop\Dtos;

/**
 * The raw catalogue filter input, already pulled out of the HTTP layer.
 *
 * PrepareShopFilterQuery used to read these off the Request itself, which tied
 * a Query to HTTP and made it unusable from a command, a job or a test without
 * faking a request. Controllers build this DTO; the Query only sees values.
 */
readonly class ShopFilterCriteriaData
{
    /** Query keys that are never a parameter code. */
    public const array RESERVED = ['brand', 'price', 'price_from', 'price_to', 'search', 'sort', 'page', 'c', 'category_id'];

    /**
     * @param  array<int, int>  $brands  brand ids, already cast to positive integers
     * @param  array<string, array<int, int>>  $attributes  option ids by parameter code; codes of no parameter are ignored later
     */
    public function __construct(
        public array $brands = [],
        public ?float $priceFrom = null,
        public ?float $priceTo = null,
        public ?string $search = null,
        public array $attributes = [],
    ) {}

    /**
     * Accepts both shapes the catalogue supports: `brand=1,2,3` and `brand[]=1&brand[]=2`
     * (see ShopProductsRequest). Parameter values come the same way under the
     * parameter's code (`season=3,4` or `season[]=3`), picked out of the whole query.
     *
     * @param  array<string, mixed>  $query  the rest of the query string
     */
    public static function fromQuery(string|array|null $brand, mixed $priceFrom, mixed $priceTo, ?string $search, array $query = []): self
    {
        return new self(
            brands: self::toIntArray($brand),
            priceFrom: $priceFrom !== null ? (float) $priceFrom : null,
            priceTo: $priceTo !== null ? (float) $priceTo : null,
            search: $search,
            attributes: collect($query)
                ->except(self::RESERVED)
                ->filter(fn ($value): bool => is_string($value) || is_array($value))
                ->map(fn ($value): array => self::toIntArray($value))
                ->filter()
                ->all(),
        );
    }

    /**
     * @return array<int, int>
     */
    private static function toIntArray(string|array|null $value): array
    {
        if (is_array($value)) {
            return collect($value)
                ->filter(fn ($item): bool => is_scalar($item))
                ->map(fn ($item): int => (int) $item)
                ->filter(fn (int $item): bool => $item > 0)
                ->values()
                ->all();
        }

        if (!$value) {
            return [];
        }

        return collect(explode(',', $value))
            ->map(fn ($item): int => (int) trim($item))
            ->filter(fn (int $item): bool => $item > 0)
            ->values()
            ->all();
    }
}
