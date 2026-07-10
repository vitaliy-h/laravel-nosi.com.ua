<?php

namespace App\Providers;

use App\Modules\Core\Enums\PageModule;
use App\Modules\Core\Models\Page;
use App\Modules\Core\Observers\LogObserver;
use App\Modules\Core\Queries\FindPageByModuleQuery;
use App\Modules\Core\Traits\Models\CascadesDeletes;
use Illuminate\Console\Application as Artisan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Throwable;

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
        'ShopOrders' => 'orders',
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
    }

    /**
     * Load routes from modules.
     */
    private function loadModuleRoutes(): void
    {
        $modulesPath = app_path('Modules');

        if (!File::exists($modulesPath)) {
            return;
        }

        $modules = File::directories($modulesPath);

        foreach ($modules as $module) {

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
     * to a PageModule (ShopOrders, _debug, Translation, Notification, ...) get a
     * prefix derived from their own folder name instead, so their routes can
     * never collide with a PageModule's DB-driven prefix or another module's
     * routes (e.g. `ShopOrders` → `/orders/...`, via MODULE_PREFIX_OVERRIDES).
     *
     * When a DB page is found for the module, the CMS page context (page,
     * breadcrumbs, meta, menus, basket count) is auto-shared to the view via the
     * `share.page` middleware, so module controllers never need to fetch/share it
     * themselves.
     */
    private function registerWebRoute(string $module, string $webRoute): void
    {
        $pageModule = PageModule::tryFrom(Str::snake(basename($module)));

        // TextPage has N pages sharing the module (no single DB-driven prefix to
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

        $route = Route::middleware(['web', 'share.page:'.$pageModule->value]);

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

    private function resolveModulePage(PageModule $pageModule): ?Page
    {
        try {
            return app(FindPageByModuleQuery::class)->handle($pageModule);
        } catch (Throwable) {
            return null;
        }
    }

    private function loadModuleCommands(): void
    {
        $modulesPath = app_path('Modules');

        if (!File::exists($modulesPath)) {
            return;
        }

        $modules = File::directories($modulesPath);

        foreach ($modules as $module) {

            $commandsPath = $module . '/Console/Commands';

            if (!File::exists($commandsPath)) {
                continue;
            }

            $files = File::allFiles($commandsPath);

            foreach ($files as $file) {

                $class = $this->getClassFromFile($file->getPathname());

                if (class_exists($class)) {
                    Artisan::starting(function ($artisan) use ($class) {
                        $artisan->resolveCommands([$class]);
                    });
                }
            }
        }
    }

    private function loadModuleMigrations(): void
    {
        $modulesPath = app_path('Modules');

        if (!File::exists($modulesPath)) {
            return;
        }

        foreach (File::directories($modulesPath) as $module) {
            $moduleName = strtolower(basename($module));

            if (isset(config('modules')[$moduleName]) && !config("modules.{$moduleName}")) {
                continue;
            }

            $migrationsPath = $module . '/database/migrations';

            if (File::exists($migrationsPath)) {
                $this->loadMigrationsFrom($migrationsPath);
            }
        }
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
            array  $data
        ) {
            app(LogObserver::class)
                ->created($data[0]);
        });

        $dispatcher->listen('eloquent.updated: *', function (
            string $event,
            array  $data
        ) {
            app(LogObserver::class)
                ->updated($data[0]);
        });

        $dispatcher->listen('eloquent.deleting: *', function (
            string $event,
            array  $data
        ) {
            $model = $data[0];

            if (in_array(CascadesDeletes::class, class_uses_recursive($model))) {
                foreach ($model->getCascadeDeletes() as $relation) {
                    $model->{$relation}()->each(fn($child) => $child->delete());
                }
            }

            app(LogObserver::class)
                ->deleting($model);
        });
    }
}
