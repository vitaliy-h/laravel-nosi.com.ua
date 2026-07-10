<?php

use App\Modules\Translation\Facade\Trans;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

function t(string $key, array $replace = []): string
{
    return Trans::get($key, $replace);
}

if (!function_exists('save_route')) {
    /**
     * Same signature as Laravel's route(), but returns '/' instead of throwing
     * when the route isn't registered (e.g. module disabled or not migrated yet).
     */
    function save_route(string $name, mixed $parameters = [], bool $absolute = true): string
    {
        if (!Route::has($name)) {
            return '/';
        }

        return route($name, $parameters, $absolute);
    }
}

if (!function_exists('image_url')) {
    function image_url(string $path, string $preset): string
    {
        return Storage::url("{$preset}/{$path}");
    }
}
