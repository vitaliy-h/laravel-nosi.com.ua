<?php

namespace App\Modules\Page\ViewModels;

use App\Modules\Core\Abstract\BaseViewModel;
use App\Modules\Core\ViewModels\BreadcrumbViewModel;
use Illuminate\Support\Collection;

/**
 * Everything `layouts/app` and its components render around the page body.
 *
 * Built once per request by PageContextSharer and shared with the view layer,
 * so a module controller never assembles the layout itself.
 */
class LayoutViewModel extends BaseViewModel
{
    /**
     * @param  Collection<int, BreadcrumbViewModel>  $breadcrumbs
     * @param  Collection<int, MenuItemViewModel>  $header_menu
     * @param  Collection<int, MenuItemViewModel>  $footer_menu
     */
    public function __construct(
        public readonly ?PageViewModel $page,
        public readonly Collection $breadcrumbs,
        public readonly ?string $meta_title,
        public readonly ?string $meta_description,
        public readonly Collection $header_menu,
        public readonly Collection $footer_menu,
        public readonly int $cart_count,
    ) {}
}
