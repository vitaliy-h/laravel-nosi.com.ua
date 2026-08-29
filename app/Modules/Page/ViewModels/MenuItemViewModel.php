<?php

namespace App\Modules\Page\ViewModels;

use App\Modules\Core\Abstract\BaseViewModel;
use App\Modules\Core\Facades\UrlFacade;
use App\Modules\Page\Models\Page;

class MenuItemViewModel extends BaseViewModel
{
    public function __construct(
        public readonly string $title,
        public readonly string $url,
        public readonly bool $is_active,
    ) {}

    public static function fromPage(Page $page): self
    {
        $url = UrlFacade::normalizeForLink($page->slug);

        return new self(
            title: (string) $page->title,
            url: $url,
            is_active: '/'.request()->path() === $url,
        );
    }
}
