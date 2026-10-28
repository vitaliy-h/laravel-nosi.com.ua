<?php

namespace App\Modules\Shop\ViewModels;

use App\Modules\Core\Abstract\BaseViewModel;
use App\Modules\Core\Support\Money;
use App\Modules\Shop\Models\AttributeOption;
use App\Modules\Shop\Models\Category;
use App\Modules\Shop\Models\Product;

class ProductViewModel extends BaseViewModel
{
    public function __construct(
        public readonly int $id,
        public readonly string $title,
        public readonly ?string $url,
        public readonly bool $has_image,
        public readonly string $image,
        public readonly string $thumb_url,
        public readonly ?string $hover_image,
        /** @var array<int, string> main photo first, then the gallery — the product page */
        public readonly array $gallery,
        public readonly ?string $brand_title,
        public readonly bool $is_hit,
        /** The price of the smallest set — what the card shows first. */
        public readonly ?float $price,
        public readonly ?string $price_formatted,
        /** «шт.» or, for a product sold in packs, «уп.». */
        public readonly string $unit,
        /** @var array<int, array{quantity: int, price: float, price_formatted: string, label: string, per_label: string}> smallest first */
        public readonly array $packs,
        /** @var array<int, array{title: string, values: string}> parameters with their chosen values — the product page */
        public readonly array $specs,
        public readonly ?string $text,
        public readonly ?string $article,
        public readonly ?CategoryViewModel $category,
        public readonly ?int $root_category_id,
    ) {}

    /**
     * The product's category has to be eager-loaded by the caller: it carries
     * the URL, and on the home page the root ancestor the isotope filter groups
     * by. Without it the product simply loses its link rather than firing a
     * query per card. The same goes for `attributeOptions.attribute` and the
     * specifications.
     */
    public static function fromProduct(Product $product): self
    {
        $category = $product->relationLoaded('category') ? $product->category : null;
        $brand = $product->relationLoaded('brand') ? $product->brand : null;
        $isPack = (bool) $product->is_pack;
        $unit = self::unit($isPack);
        $per = t('shop.price_per') ?: 'за';

        return new self(
            id: $product->id,
            title: (string) $product->title,
            url: $category
                ? save_route('shop.product', [$category->slug, $product->slug_with_id])
                : null,
            has_image: !empty($product->img),
            image: $product->image,
            thumb_url: $product->imageUrl('thumb'),
            hover_image: $product->images[0] ?? null,
            gallery: !empty($product->img) ? [$product->image, ...$product->images] : $product->images,
            brand_title: $brand?->title,
            is_hit: (bool) $product->is_hit,
            price: $product->price,
            price_formatted: $product->price !== null ? Money::from($product->price)->format() : null,
            unit: $unit,
            packs: array_map(fn (array $pack): array => [
                'quantity'        => $pack['quantity'],
                'price'           => $pack['price'],
                'price_formatted' => Money::from($pack['price'])->format(),
                'label'           => $pack['quantity'] . ' ' . $unit,
                // a pack product: «за уп. (5пар)»; otherwise «за шт.» for a single piece, «за 5 шт.» for a set
                'per_label' => $isPack
                    ? $per . ' ' . (t('shop.unit_box') ?: 'уп.') . ' (' . $pack['quantity'] .' '. $unit . ')'
                    : $per . ' ' . ($pack['quantity'] > 1 ? $pack['quantity'] . ' ' : '') . $unit,
            ], Product::normalizePacks($product->packs)),
            specs: self::specs($product),
            text: $product->text,
            article: $product->article,
            category: $category ? CategoryViewModel::from($category) : null,
            root_category_id: self::rootCategoryId($category),
        );
    }

    /**
     * The unit word next to a set size. The dictionary may not have the key yet
     * on a database seeded before it was added — the word is never left empty.
     */
    public static function unit(bool $isPack): string
    {
        return $isPack
            ? (t('shop.unit_pack') ?: 'уп.')
            : (t('shop.unit_piece') ?: 'шт.');
    }

    /**
     * @return array<int, array{title: string, values: string}>
     */
    private static function specs(Product $product): array
    {
        if (!$product->relationLoaded('attributeOptions')) {
            return [];
        }

        return $product->attributeOptions
            ->filter(fn (AttributeOption $option): bool => $option->relationLoaded('attribute')
                && $option->attribute?->display)
            ->sortBy([
                fn (AttributeOption $a, AttributeOption $b): int => [-(int) $a->attribute->rank, $a->attribute_id] <=> [-(int) $b->attribute->rank, $b->attribute_id],
                fn (AttributeOption $a, AttributeOption $b): int => [(int) $a->rank, $a->id] <=> [(int) $b->rank, $b->id],
            ])
            ->groupBy('attribute_id')
            ->map(fn ($options): array => [
                'title'  => (string) $options->first()->attribute->title,
                'values' => $options->pluck('title')->filter()->implode(', '),
            ])
            ->values()
            ->all();
    }

    /**
     * The top-level category a product hangs under, for the home page filter.
     * Needs `category.ancestors` eager-loaded; without it a nested category
     * reports no root rather than walking the tree per product.
     */
    private static function rootCategoryId(?Category $category): ?int
    {
        if ($category === null) {
            return null;
        }

        if ($category->isRoot()) {
            return $category->id;
        }

        return $category->relationLoaded('ancestors')
            ? $category->ancestors->first()?->id
            : null;
    }
}
