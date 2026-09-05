<?php

namespace App\Providers;

use App\Modules\Core\Listeners\LogListener;
use App\Modules\Core\Support\ModuleGate;
use App\Modules\Page\Enums\PageModule;
use App\Modules\Page\Models\Page;
use App\Modules\Page\Queries\FindPageByModuleQuery;
use Illuminate\Console\Application as Artisan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class ModuleServiceProvider extends ServiceProvider
{
    /**
     * Modules that must stay mounted at the app root instead of getting an
     * auto-derived folder-name prefix: Core owns `/storage/{preset}/{path}`
     * (image previews) and Sitemap owns the SEO-standard `/sitemap*.xml` paths.
     * Prefixing either would break those conventions.
     */
    private const array ROOT_LEVEL_MODULES = ['Core', 'Sitemap'];

    /**
     * Folder-name overrides for the URL prefix of unmapped modules (see
     * registerUnmappedModuleRoute()), so renaming a module folder doesn't
     * silently change its public URLs.
     */
    private const array MODULE_PREFIX_OVERRIDES = [
        'ShopOrder' => 'orders',
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->registerLogListeners();

        $this->loadModuleRoutes();

        $this->loadModuleCommands();

        $this->loadModuleMigrations();

        $this->loadModuleObservers();
    }

    /**
     * Absolute paths of the enabled modules, resolved once per request.
     *
     * boot() needs this list four times (routes, commands, migrations,
     * observers); scanning app/Modules and re-asking ModuleGate each time cost
     * four directory listings on every request for the same answer.
     *
     * @var array<int, string>|null
     */
    private ?array $enabledModules = null;

    /**
     * @return array<int, string>
     */
    private function enabledModules(): array
    {
        if ($this->enabledModules !== null) {
            return $this->enabledModules;
        }

        $modulesPath = app_path('Modules');

        if (!File::exists($modulesPath)) {
            return $this->enabledModules = [];
        }

        return $this->enabledModules = array_values(array_filter(
            File::directories($modulesPath),
            fn (string $module): bool => ModuleGate::isEnabled(Str::snake(basename($module))),
        ));
    }

    /**
     * Load routes from modules.
     */
    private function loadModuleRoutes(): void
    {
        foreach ($this->enabledModules() as $module) {

            $webRoute = $module . '/Http/Routes/web.php';
            $apiRoute = $module . '/Http/Routes/api.php';

            if (File::exists($webRoute)) {
                $this->registerWebRoute($module, $webRoute);
            }

            if (File::exists($apiRoute)) {
                Route::prefix('api')
                    ->middleware('api')
                    ->group($apiRoute);
            }
        }
    }

    /**
     * Register a module's web routes, prefixed by the module's page slug (from DB)
     * when the module directory maps to a PageModule case. Modules that don't map
     * to a PageModule (ShopOrder, _debug, Translation, Notification, ...) get a
     * prefix derived from their own folder name instead, so their routes can
     * never collide with a PageModule's DB-driven prefix or another module's
     * routes (e.g. `ShopOrder` → `/orders/...`, via MODULE_PREFIX_OVERRIDES).
     *
     * When a DB page is found for the module, the CMS page context (page,
     * breadcrumbs, meta, menus, cart count) is auto-shared to the view via the
     * `share.page` middleware, so module controllers never need to fetch/share it
     * themselves.
     */
    private function registerWebRoute(string $module, string $webRoute): void
    {
        $pageModule = PageModule::tryFrom(Str::snake(basename($module)));

        // PageText has N pages sharing the module (no single DB-driven prefix to
        // resolve, each page has its own independent slug) so it skips prefix/
        // share.page resolution and registers its own fallback route instead.
        if ($pageModule === PageModule::TEXT) {
            Route::middleware('web')->group($webRoute);

            return;
        }

        if (!$pageModule) {
            $this->registerUnmappedModuleRoute($module, $webRoute);

            return;
        }

        $page = $this->resolveModulePage($pageModule);

        $prefix = $page?->slug;

        if ($prefix === null || $prefix === '') {
            $prefix = $pageModule === PageModule::INDEX ? '' : Str::lower($pageModule->value);
        }

        $route = Route::middleware(['web', 'share.page:' . $pageModule->value]);

        if ($prefix !== '') {
            $route = $route->prefix($prefix);
        }

        $route->group($webRoute);
    }

    private function registerUnmappedModuleRoute(string $module, string $webRoute): void
    {
        $name = basename($module);

        if (in_array($name, self::ROOT_LEVEL_MODULES, true)) {
            Route::middleware('web')->group($webRoute);

            return;
        }

        $prefix = self::MODULE_PREFIX_OVERRIDES[$name] ?? Str::kebab($name);

        Route::middleware('web')->prefix($prefix)->group($webRoute);
    }

    /**
     * The page rows come from GetAllPagesQuery, which caches them for 24h and is
     * a singleton, so this costs one cache read per request rather than a query
     * per module.
     *
     * Only a database failure is swallowed — a missing `pages` table on a fresh
     * install, or no connection yet. Anything else (a broken query class, a bad
     * cache driver) must surface instead of silently mounting every module under
     * its fallback prefix.
     */
    private function resolveModulePage(PageModule $pageModule): ?Page
    {
        try {
            return app(FindPageByModuleQuery::class)->handle($pageModule);
        } catch (QueryException) {
            return null;
        }
    }

    /**
     * Every module command is handed to Artisan in one `starting` callback:
     * registering a closure per command file meant dozens of callbacks that all
     * ran on every console boot for the same result.
     */
    private function loadModuleCommands(): void
    {
        $classes = [];

        foreach ($this->enabledModules() as $module) {
            $commandsPath = $module . '/Console/Commands';

            if (!File::exists($commandsPath)) {
                continue;
            }

            foreach (File::allFiles($commandsPath) as $file) {
                $class = $this->getClassFromFile($file->getPathname());

                if (class_exists($class)) {
                    $classes[] = $class;
                }
            }
        }

        if ($classes === []) {
            return;
        }

        Artisan::starting(static function ($artisan) use ($classes): void {
            $artisan->resolveCommands($classes);
        });
    }

    private function loadModuleMigrations(): void
    {
        foreach ($this->enabledModules() as $module) {
            $migrationsPath = $module . '/database/migrations';

            if (File::exists($migrationsPath)) {
                $this->loadMigrationsFrom($migrationsPath);
            }
        }
    }

    /**
     * Register each enabled module's model observers by convention:
     * `{Module}/Observers/{Model}Observer.php` is attached to
     * `App\Modules\{Module}\Models\{Model}`. Registering them here instead of
     * in AppServiceProvider is what makes a disabled module's folder deletable —
     * nothing outside the module names its observers or models (see
     * docs/starter/modules.md §4).
     *
     * An observer whose matching model class doesn't exist is skipped, so a
     * non-model listener must not live in `Observers/`
     * (Core\Listeners\LogListener is the one such listener, wired globally in
     * registerLogListeners()).
     */
    private function loadModuleObservers(): void
    {
        foreach ($this->enabledModules() as $module) {
            $observersPath = $module . '/Observers';

            if (!File::exists($observersPath)) {
                continue;
            }

            foreach (File::files($observersPath) as $file) {
                $observer = $this->getClassFromFile($file->getPathname());
                $model = $this->resolveObservedModel($observer);

                if ($model !== null && class_exists($observer)) {
                    $model::observe($observer);
                }
            }
        }
    }

    /**
     * @param  class-string  $observer
     * @return class-string<Model>|null
     */
    private function resolveObservedModel(string $observer): ?string
    {
        $model = str($observer)
            ->replace('\\Observers\\', '\\Models\\')
            ->replaceMatches('/Observer$/', '')
            ->toString();

        return class_exists($model) && is_subclass_of($model, Model::class)
            ? $model
            : null;
    }

    private function getClassFromFile(string $path): string
    {
        $appPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, app_path());
        $normalizedPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);

        $relative = str($normalizedPath)
            ->after($appPath . DIRECTORY_SEPARATOR)
            ->replace(DIRECTORY_SEPARATOR, '\\')
            ->replace('.php', '')
            ->toString();

        return 'App\\' . $relative;
    }

    private function registerLogListeners(): void
    {
        $dispatcher = Model::getEventDispatcher();

        $dispatcher->listen('eloquent.created: *', function (
            string $event,
            array $data
        ) {
            app(LogListener::class)
                ->created($data[0]);
        });

        $dispatcher->listen('eloquent.updated: *', function (
            string $event,
            array $data
        ) {
            app(LogListener::class)
                ->updated($data[0]);
        });

        $dispatcher->listen('eloquent.deleting: *', function (
            string $event,
            array $data
        ) {
            app(LogListener::class)
                ->deleting($data[0]);
        });
    }
}
