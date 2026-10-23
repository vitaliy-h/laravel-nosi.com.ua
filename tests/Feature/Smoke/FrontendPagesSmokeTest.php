<?php

namespace Tests\Feature\Smoke;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route as RouteFacade;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Walks the routes the app actually registered instead of a hand-written list.
 *
 * Module prefixes come from the `pages` table at boot (see docs/starter/routing.md),
 * so a hard-coded `/shop_cart` was really asserting the fallback prefix a test
 * environment happens to produce. Reading the route table keeps this honest and
 * picks up new modules without anyone remembering to add them here.
 */
class FrontendPagesSmokeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Prefixes that are not frontend pages: the admin panel and its assets, the
     * API (covered by its own tests), the super-admin-only debug module, and the
     * XML sitemaps (covered by SitemapTest).
     */
    private const array SKIPPED_PREFIXES = [
        'admin', 'api', '_debug', 'livewire', 'storage', 'up', 'sanctum', 'sitemap',
    ];

    /**
     * Named routes that answer AJAX rather than render a page, so they need
     * query parameters and legitimately 404 without them.
     */
    private const array SKIPPED_ROUTE_NAMES = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedPagesForRouting();
    }

    public function test_every_registered_frontend_page_responds_without_a_server_error(): void
    {
        $urls = $this->frontendUrls();

        $this->assertNotEmpty($urls, 'No frontend routes were registered at all.');

        $failures = [];

        foreach ($urls as $url) {
            $status = $this->get($url)->status();

            if ($status >= 500) {
                $failures[] = "{$url} → {$status}";
            }
        }

        $this->assertSame([], $failures, "Frontend pages returned a server error:\n" . implode("\n", $failures));
    }

    public function test_every_registered_frontend_page_is_reachable(): void
    {
        $unreachable = [];

        foreach ($this->frontendUrls() as $url) {
            $status = $this->get($url)->status();

            // 200 renders, 302 is a guest redirect (/profile for a guest).
            if (!in_array($status, [200, 302], true)) {
                $unreachable[] = "{$url} → {$status}";
            }
        }

        $this->assertSame([], $unreachable, "Unexpected statuses:\n" . implode("\n", $unreachable));
    }

    /**
     * PageText owns the app's single Route::fallback(), so its pages are not in
     * the route table — they resolve by slug at request time.
     */
    public function test_a_cms_text_page_renders_through_the_fallback_route(): void
    {
        $this->get('/dostavka-ta-oplata')->assertOk();
    }

    /**
     * GET routes with no parameters, outside the skipped prefixes.
     *
     * @return array<int, string>
     */
    private function frontendUrls(): array
    {
        return collect(RouteFacade::getRoutes()->getRoutes())
            ->filter(fn (Route $route): bool => in_array('GET', $route->methods(), true))
            ->reject(fn (Route $route): bool => Str::contains($route->uri(), '{'))
            ->reject(fn (Route $route): bool => Str::startsWith($route->uri(), self::SKIPPED_PREFIXES))
            ->reject(fn (Route $route): bool => in_array($route->getName(), self::SKIPPED_ROUTE_NAMES, true))
            ->map(fn (Route $route): string => '/' . ltrim($route->uri(), '/'))
            ->unique()
            ->values()
            ->all();
    }
}
