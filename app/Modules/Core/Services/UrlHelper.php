<?php

namespace App\Modules\Core\Services;

use App\Modules\Core\Enums\PageModule;
use App\Modules\Core\Repositories\PagesRepository;
use Illuminate\Support\Str;

class UrlHelper
{
    public function __construct(
        private PagesRepository $pages
    )
    {
    }

    /**
     * Normalize raw URL string (trim + clean slashes).
     */
    public function normalize(?string $url = ''): string
    {
        $url = trim($url);

        return trim($url, '/');
    }

    /**
     * Normalize URL for usage in links (/path/to/page).
     */
    public function normalizeForLink(?string $url = ''): string
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

    /**
     * Get module url helper
     * @param PageModule $module
     * @param array|string $params
     * @param array $query
     * @return string
     */
    public function getModuleUrl(PageModule $module, array|string $params = [], array $query = []): string
    {
        $page = $this->pages->findByModule($module);

        if (!$page) {
            return '#';
        }

        $url = $page->slug;

        /*
        |--------------------------------------------------------------------------
        | Route params
        |--------------------------------------------------------------------------
        */

        if (is_string($params)) {
            $params = [$params];
        }

        foreach ($params as $param) {
            $url .= '/' . trim($param, '/');
        }

        $url = $this->normalizeForLink($url);

        /*
        |--------------------------------------------------------------------------
        | Query params
        |--------------------------------------------------------------------------
        */

        if (!empty($query)) {
            $queryString = collect($query)
                ->map(function ($value, $key) {

                    if ($value === null || $value === '') {
                        return $key;
                    }

                    return $key . '=' . urlencode($value);

                })
                ->implode('&');

            $url .= '?' . $queryString;
        }

        return url($url);
    }
}
