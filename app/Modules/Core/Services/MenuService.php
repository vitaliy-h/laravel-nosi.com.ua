<?php

namespace App\Modules\Core\Services;

use App\Modules\Core\Models\Page;

class MenuService
{
    public function getHeaderMenu()
    {
        return Page::query()
            ->visible()
            ->where('display_menu', true)
            ->orderBy('rank','DESC')
            ->get();
    }

    public function getFooterMenu()
    {
        return Page::query()
            ->visible()
            ->where('display_menu_footer', true)
            ->orderBy('rank','DESC')
            ->get();
    }
}
