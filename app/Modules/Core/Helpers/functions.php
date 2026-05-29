<?php

use App\Modules\Core\Enums\PageModule;
use App\Modules\Translation\Facade\Trans;

function t(string $key, array $replace = []): string
{
    return Trans::get($key, $replace);
}

if (!function_exists('module_url')) {
    function module_url(PageModule $module, array|string $params = [], array $query = []): string
    {
        return \App\Modules\Core\Facades\UrlFacade::getModuleUrl($module, $params, $query);
    }
}
