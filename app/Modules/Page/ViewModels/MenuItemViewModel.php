<?php

namespace App\Modules\Page\ViewModels;

use App\Modules\Core\Abstract\BaseViewModel;
use App\Modules\Core\Support\UrlHelper;
use App\Modules\Page\Models\Page;
use App\Modules\Shop\Models\Category;

class MenuItemViewModel extends BaseViewModel
{
    public function __construct(
        public readonly string $title,
        public readonly string $url,
        public readonly bool $is_active,
        /** @var array<int, MenuItemViewModel> the dropdown under a header category */
        public readonly array $children = [],
    ) {}

    /**
     * $currentPath is the path of the request being rendered, leading slash
     * included ('/about', '/' for the home page). It is passed in rather than read
     * off `request()`: a ViewModel gets everything it renders from its caller.
     */
    public static function fromPage(Page $page, string $currentPath): self
    {
        $url = UrlHelper::normalizeForLink($page->slug);

        return new self(
            title: (string) $page->title,
            url: $url,
            is_active: $currentPath === $url,
        );
    }

    /**
     * Direct children become the dropdown when the caller has loaded them
     * (MenuService::getHeaderCategories); the item is active when it or one of
     * them is the current page.
     */
    public static function fromCategory(Category $category, string $currentPath): self
    {
        $url = save_route('shop.category', $category->slug, false);

        $children = $category->relationLoaded('children')
            ? $category->children
                ->map(fn (Category $child) => new self(
                    title: (string) $child->title,
                    url: $childUrl = save_route('shop.category', $child->slug, false),
                    is_active: $currentPath === $childUrl,
                ))
                ->values()
                ->all()
            : [];

        return new self(
            title: (string) $category->title,
            url: $url,
            is_active: $currentPath === $url || collect($children)->contains('is_active', true),
            children: $children,
        );
    }
}
