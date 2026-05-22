<?php

namespace App\Modules\Core\Services;

use Illuminate\Support\Str;

class UrlHelper
{
    /**
     * Normalize raw URL string (trim + clean slashes).
     */
    public function normalize(string $url): string
    {
        $url = trim($url);

        return trim($url, '/');
    }

    /**
     * Normalize URL for usage in links (/path/to/page).
     */
    public function normalizeForLink(string $url): string
    {
        $url = $this->normalize($url);

        return $url === '' ? '/' : '/' . $url;
    }

    /**
     * Generate slug with support for nested paths (parent/child-page).
     */
    public function slug(string $value): string
    {
        $value = trim($value);

        $segments = array_filter(explode('/', $value));

        $segments = array_map(function ($segment) {
            return Str::slug($segment);
        }, $segments);

        return implode('/', $segments);
    }
}
