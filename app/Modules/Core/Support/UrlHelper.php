<?php

namespace App\Modules\Core\Support;

use Illuminate\Support\Str;

/**
 * Pure URL string helpers: static because they hold no state and need no
 * dependencies — there is nothing for a container binding or a facade to add.
 */
class UrlHelper
{
    /**
     * Normalize raw URL string (trim + clean slashes).
     */
    public static function normalize(?string $url = ''): string
    {
        $url = trim($url);

        return trim($url, '/');
    }

    /**
     * Normalize URL for usage in links (/path/to/page).
     */
    public static function normalizeForLink(?string $url = ''): string
    {
        $url = self::normalize($url);

        return $url === '' ? '/' : '/' . $url;
    }

    /**
     * Generate slug with support for nested paths (parent/child-page).
     */
    public static function slug(string $value): string
    {
        $value = trim($value);

        $segments = array_filter(explode('/', $value));

        $segments = array_map(function ($segment) {
            return Str::slug($segment);
        }, $segments);

        return implode('/', $segments);
    }
}
