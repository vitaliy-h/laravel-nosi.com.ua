<?php

namespace App\Modules\Page\ViewModels;

use App\Modules\Core\Abstract\BaseViewModel;
use App\Modules\Core\Facades\UrlFacade;
use App\Modules\Page\Models\Page;

/**
 * The CMS page behind the current screen.
 *
 * Meta fields are deliberately absent: the layout takes its meta from
 * LayoutViewModel, because a module may override it with its own entity's meta
 * (a news item, a product) while the CMS page stays the same.
 */
class PageViewModel extends BaseViewModel
{
    /**
     * @param  array<int, array<string, mixed>>  $content  builder blocks
     */
    public function __construct(
        public readonly string $title,
        public readonly string $url,
        public readonly array $content,
        public readonly bool $is_indexable,
    ) {}

    public static function fromPage(Page $page): self
    {
        return new self(
            title: (string) $page->title,
            url: UrlFacade::normalizeForLink($page->slug),
            content: $page->content ?? [],
            is_indexable: (bool) $page->is_indexable,
        );
    }
}
