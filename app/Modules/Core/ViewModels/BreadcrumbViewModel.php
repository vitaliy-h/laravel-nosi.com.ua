<?php

namespace App\Modules\Core\ViewModels;

use App\Modules\Core\Abstract\BaseViewModel;

class BreadcrumbViewModel extends BaseViewModel
{
    public function __construct(
        public readonly string $title,
        public readonly string $url,
    ) {}
}
