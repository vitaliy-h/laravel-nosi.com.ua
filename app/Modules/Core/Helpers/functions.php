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

if (!function_exists('is_active_url')) {
    /**
     * Whether a menu link points at the current page or at a section the page belongs to
     * (`/shop/kolgotky` is active on `/shop/kolgotky/some-product`). The home page and
     * links with an anchor (`/#sale`) match only exactly / never.
     */
    function is_active_url(?string $url): bool
    {
        if (blank($url) || $url === '#' || str_contains($url, '#')) {
            return false;
        }

        $host = parse_url($url, PHP_URL_HOST);

        if ($host !== null && $host !== request()->getHost()) {
            return false;
        }

        $path = trim((string) parse_url($url, PHP_URL_PATH), '/');
        $current = trim(request()->path(), '/');

        if ($path === '') {
            return $current === '';
        }

        return $current === $path || str_starts_with($current . '/', $path . '/');
    }
}
