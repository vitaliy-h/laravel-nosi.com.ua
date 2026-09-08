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
    /**
     * @param  array<int, int>  $brands  brand ids, already cast to positive integers
     */
    public function __construct(
        public array $brands = [],
        public ?float $priceFrom = null,
        public ?float $priceTo = null,
        public ?string $search = null,
    ) {}

    /**
     * Accepts both shapes the catalogue supports: `brand=1,2,3` and `brand[]=1&brand[]=2`
     * (see ShopProductsRequest).
     */
    public static function fromQuery(string|array|null $brand, mixed $priceFrom, mixed $priceTo, ?string $search): self
    {
        return new self(
            brands: self::toIntArray($brand),
            priceFrom: $priceFrom !== null ? (float) $priceFrom : null,
            priceTo: $priceTo !== null ? (float) $priceTo : null,
            search: $search,
        );
    }

    /**
     * @return array<int, int>
     */
    private static function toIntArray(string|array|null $value): array
    {
        if (is_array($value)) {
            return collect($value)
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
