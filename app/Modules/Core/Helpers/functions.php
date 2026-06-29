<?php

use App\Modules\Core\Enums\PageModule;
use App\Modules\Translation\Facade\Trans;
use Illuminate\Support\Facades\Storage;

function t(string $key, array $replace = []): string
{
    return Trans::get($key, $replace);
}

if (!function_exists('module_url')) {
    function module_url(PageModule|string $module, array|string $params = [], array $query = []): string
    {
        return \App\Modules\Core\Facades\UrlFacade::getModuleUrl($module, $params, $query);
    }
}

if (!function_exists('image_url')) {
    function image_url(string $path, string $preset): string
    {
        return Storage::url("{$preset}/{$path}");
    }
}
