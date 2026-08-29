<?php

namespace App\Modules\Shop\ViewModels;

use App\Modules\Core\Abstract\BaseViewModel;
use App\Modules\Shop\Models\Category;
use Illuminate\Support\Collection;

class CategoryViewModel extends BaseViewModel
{
    /**
     * @param  Collection<int, CategoryViewModel>  $children
     */
    public function __construct(
        public readonly int $id,
        public readonly string $title,
        public readonly string $slug,
        public readonly string $url,
        public readonly string $image,
        public readonly ?string $text,
        public readonly int $products_count,
        public readonly bool $is_active,
        public readonly bool $is_opened,
        public readonly Collection $children,
    ) {}

    /**
     * `_active` / `_opened` and `products_count` are set on the model by
     * GetAllCategoriesQuery when it builds the sidebar tree; elsewhere they are
     * simply absent.
     */
    public static function fromCategory(Category $category): self
    {
        return new self(
            id: $category->id,
            title: (string) $category->title,
            slug: (string) $category->slug,
            url: save_route('shop.category', $category->slug),
            image: $category->image,
            text: $category->text,
            products_count: (int) ($category->products_count ?? 0),
            is_active: (int) ($category->_active ?? 0) === 1,
            is_opened: (int) ($category->_opened ?? 0) === 1,
            children: $category->relationLoaded('children')
                ? $category->children->map(fn (Category $child) => self::fromCategory($child))->values()
                : collect(),
        );
    }
}
