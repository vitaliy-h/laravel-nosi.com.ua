<?php

namespace App\Modules\Page\ViewModels;

use App\Modules\Core\Abstract\BaseViewModel;
use App\Modules\Core\Support\UrlHelper;
use App\Modules\Page\Models\Page;

class MenuItemViewModel extends BaseViewModel
{
    public function __construct(
        public readonly string $title,
        public readonly string $url,
        public readonly bool $is_active,
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
}
