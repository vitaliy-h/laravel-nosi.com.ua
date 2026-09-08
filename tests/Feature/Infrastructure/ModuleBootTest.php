<?php

namespace Tests\Feature\Infrastructure;

use App\Modules\Core\Support\ModuleManifest;
use App\Modules\Page\Models\Page;
use App\Modules\Page\Queries\GetAllPagesQuery;
use App\Modules\ShopCart\Console\Commands\ClearAbandonedCartsCommand;
use App\Modules\ShopCart\Models\Cart;
use App\Modules\ShopCart\Observers\CartObserver;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use PDOException;
use ReflectionProperty;
use RuntimeException;
use Tests\TestCase;

/**
 * The manifest is what the application boots from, so a mistake here does not
 * break a feature — it breaks every request. These cover what the boot used to
 * work out inline: which modules count, and where each one's routes mount.
 */
class ModuleBootTest extends TestCase
{
    use RefreshDatabase;

    private string $cachePath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cachePath = storage_path('framework/testing/modules.php');

        File::delete($this->cachePath);
    }

    protected function tearDown(): void
    {
        File::delete($this->cachePath);

        parent::tearDown();
    }

    private function manifest(): ModuleManifest
    {
        return new ModuleManifest(app_path('Modules'), $this->cachePath);
    }

    /**
     * News stands in for an optional module throughout this class: the manifest
     * is generic, but an assertion has to name something, and a module that is
     * switched off is genuinely absent from the manifest under test.
     */
    public function test_it_lists_the_enabled_modules(): void
    {
        $this->skipUnlessModules('news');

        $modules = $this->manifest()->build();

        $this->assertArrayHasKey('Page', $modules);
        $this->assertArrayHasKey('News', $modules);
    }

    public function test_it_skips_a_disabled_module(): void
    {
        config(['modules.list.news' => false]);

        $modules = $this->manifest()->build();

        $this->assertArrayNotHasKey('News', $modules);
        $this->assertArrayHasKey('Page', $modules);
    }

    public function test_a_page_driven_module_is_mounted_behind_its_page_slug(): void
    {
        $this->skipUnlessModules('news');

        Page::factory()->create(['module' => 'news', 'slug' => 'articles']);

        $news = $this->manifest()->build()['News'];

        $this->assertSame('articles', $news['web']['prefix']);
        $this->assertSame('news', $news['web']['page_module']);
    }

    /**
     * Tests seed their pages after the app has booted, so the fallback is what
     * they hit — `/news/...` rather than the slug an admin would have set.
     */
    public function test_a_module_without_a_page_row_falls_back_to_its_own_name(): void
    {
        $this->skipUnlessModules('news');

        $news = $this->manifest()->build()['News'];

        $this->assertSame('news', $news['web']['prefix']);
    }

    public function test_an_unmapped_module_is_prefixed_from_its_folder_name(): void
    {
        $this->skipUnlessModules('shop_order', 'subscriber');

        $modules = $this->manifest()->build();

        // ShopOrder has an explicit override so its URLs don't read /shop-order.
        $this->assertSame('orders', $modules['ShopOrder']['web']['prefix']);
        $this->assertSame('subscriber', $modules['Subscriber']['web']['prefix']);
        $this->assertNull($modules['ShopOrder']['web']['page_module']);
    }

    public function test_root_level_modules_keep_the_app_root(): void
    {
        $modules = $this->manifest()->build();

        $this->assertSame('', $modules['Core']['web']['prefix']);
        $this->assertSame('', $modules['Sitemap']['web']['prefix']);
    }

    public function test_a_module_knows_its_commands_migrations_and_observers(): void
    {
        $this->skipUnlessModules('shop_cart');

        $cart = $this->manifest()->build()['ShopCart'];

        $this->assertContains(
            ClearAbandonedCartsCommand::class,
            $cart['commands'],
        );
        $this->assertNotNull($cart['migrations']);
        $this->assertSame(
            Cart::class,
            $cart['observers'][CartObserver::class] ?? null,
        );
    }

    public function test_module_commands_are_registered(): void
    {
        $this->skipUnlessModules('shop_cart');

        $this->assertArrayHasKey('cart:clear-abandoned', Artisan::all());
    }

    public function test_a_cached_manifest_is_what_gets_loaded(): void
    {
        $manifest = $this->manifest();

        $this->assertFalse($manifest->isCached());

        $manifest->write();

        $this->assertTrue($manifest->isCached());
        $this->assertSame($manifest->build(), $this->manifest()->load());

        $manifest->clear();

        $this->assertFalse($manifest->isCached());
    }

    /**
     * A page slug is a route prefix, so a cached manifest has to be rebuilt when
     * one changes — that is what PageObserver asks for.
     */
    public function test_refresh_rewrites_a_cached_manifest_and_leaves_an_uncached_one_alone(): void
    {
        $this->skipUnlessModules('news');

        $page = Page::factory()->create(['module' => 'news', 'slug' => 'articles']);

        $manifest = $this->manifest();
        $manifest->write();

        $page->update(['slug' => 'journal']);
        $manifest->refresh();

        $this->assertSame('journal', $this->manifest()->load()['News']['web']['prefix']);

        $manifest->clear();
        $manifest->refresh();

        $this->assertFalse($manifest->isCached());
    }

    /**
     * A manifest holds route prefixes read from a database. The one a developer
     * caches locally therefore describes the development database, and booting a
     * test run from it would mount every page-driven module behind the wrong
     * URL — so the test run always builds its own.
     */
    public function test_a_test_run_ignores_a_manifest_cached_on_disk(): void
    {
        $manifest = new ModuleManifest(app_path('Modules'), $this->cachePath);
        $manifest->write();

        $ignoring = new ModuleManifest(app_path('Modules'), $this->cachePath, useCache: false);

        $this->assertTrue($ignoring->isCached());
        $this->assertSame($ignoring->build(), $ignoring->load());

        // This is what the container hands the application under PHPUnit.
        $this->assertFalse(
            (new ReflectionProperty(app(ModuleManifest::class), 'useCache'))
                ->getValue(app(ModuleManifest::class)),
        );
    }

    public function test_a_database_failure_leaves_the_module_without_a_page(): void
    {
        $this->skipUnlessModules('news');

        $this->mock(GetAllPagesQuery::class)
            ->shouldReceive('handle')
            ->andThrow(new QueryException('mysql', 'select 1', [], new PDOException('no connection')));

        // Falls back to the module name instead of taking the whole app down:
        // on a fresh clone the pages table does not exist yet.
        $this->assertSame('news', $this->manifest()->build()['News']['web']['prefix']);
    }

    public function test_a_non_database_failure_is_not_swallowed(): void
    {
        $this->mock(GetAllPagesQuery::class)
            ->shouldReceive('handle')
            ->andThrow(new RuntimeException('something else broke'));

        $this->expectException(RuntimeException::class);

        $this->manifest()->build();
    }
}
