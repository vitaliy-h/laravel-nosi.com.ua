<?php

namespace App\Modules\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * One address per page for search engines: with APP_CANONICAL_URL set (production),
 * a GET/HEAD request is sent with a 301 to that scheme and host — http → https,
 * www → bare domain — and to a clean path: no `/public` or `/index.php` prefix
 * (the project sits in public_html and the root .htaccess rewrites into public/),
 * no trailing slash. Unset (local, tests) it does nothing.
 */
class RedirectToCanonicalUrl
{
    public function handle(Request $request, Closure $next): Response
    {
        $canonical = rtrim((string) config('app.canonical_url'), '/');

        if ($canonical === '' || !$request->isMethodSafe()) {
            return $next($request);
        }

        $uri = $request->getRequestUri();
        $path = (string) parse_url($uri, PHP_URL_PATH);
        $query = parse_url($uri, PHP_URL_QUERY);

        $cleanPath = preg_replace(['#^/public(?=/|$)#', '#^/index\.php(?=/|$)#'], '', $path);
        $cleanPath = '/' . trim((string) $cleanPath, '/');

        $sameOrigin = $request->getScheme() === parse_url($canonical, PHP_URL_SCHEME)
            && $request->getHost() === parse_url($canonical, PHP_URL_HOST);

        if ($sameOrigin && $cleanPath === $path) {
            return $next($request);
        }

        $target = $canonical . ($cleanPath === '/' ? '' : $cleanPath) . ($query ? '?' . $query : '');

        return redirect()->to($target ?: '/', 301);
    }
}
