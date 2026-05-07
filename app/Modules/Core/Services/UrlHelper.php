<?php

namespace App\Modules\Core\Services;

class UrlHelper
{
    /**
     *  Normalize a URL path to a canonical format (leading slash, no trailing slash).
     *
     * @param string $url
     * @return string
     */
    public function normalize(string $url): string
    {
        $url = trim($url);

        $url = '/' . ltrim($url, '/');
        $url = rtrim($url, '/');

        return $url === '' ? '/' : $url;
    }
}
