<?php

namespace App\Providers;

use App\Modules\Core\Listeners\LogListener;
use App\Modules\Core\Support\ModuleManifest;
use Illuminate\Console\Application as Artisan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

/**
 * Mounts every enabled module: its routes, migrations, observers and commands.
 *
 * What belongs to which module — and, for a page-driven module, which URL prefix
 * it sits behind — is worked out by `ModuleManifest`, not here. This class only
 * registers what the manifest describes, so booting neither scans the module
 * folders nor queries the database once the manifest is cached
 * (`php artisan modules:cache`). See `docs/starter/modules.md`.
 */
class ModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(
            ModuleManifest::class,
            fn (): ModuleManifest => new ModuleManifest(
                app_path('Modules'),
                $this->app->bootstrapPath('cache/modules.php'),
                useCache: !$this->app->runningUnitTests(),
            ),
        );
    }

    public function boot(): void
    {
        $this->registerLogListeners();

        $manifest = $this->app->make(ModuleManifest::class)->load();

        $commands = [];

        foreach ($manifest as $module) {
            $this->registerRoutes($module);
            $this->registerMigrations($module);
            $this->registerObservers($module);

            $commands = [...$commands, ...$module['commands']];
        }

        // Not only in the console: admin → Debug Tools runs whitelisted module
        // commands (telegram:webhook) through Artisan::call() from a web request.
        // Free for an ordinary page — the classes are resolved in Artisan::starting,
        // which fires only once Artisan itself boots.
        $this->registerCommands($commands);
    }

    /**
     * @param  array<string, mixed>  $module
     */
    private function registerRoutes(array $module): void
    {
        if ($module['web'] !== null) {
            $web = $module['web'];

            // One array, never two calls: RouteRegistrar::attribute() replaces
            // the middleware attribute instead of merging it.
            $route = Route::middleware(array_filter([
                'web',
                $web['page_module'] !== null ? 'share.page:' . $web['page_module'] : null,
            ]));

            if ($web['prefix'] !== '') {
                $route = $route->prefix($web['prefix']);
            }

            $route->group($web['path']);
        }

        if ($module['api'] !== null) {
            Route::prefix('api')
                ->middleware('api')
                ->group($module['api']);
        }
    }

    /**
     * @param  array<string, mixed>  $module
     */
    private function registerMigrations(array $module): void
    {
        if ($module['migrations'] !== null) {
            $this->loadMigrationsFrom($module['migrations']);
        }
    }

    /**
     * Registering observers here instead of in AppServiceProvider is what makes
     * a disabled module's folder deletable — nothing outside the module names
     * its observers or models (see docs/starter/modules.md §4).
     *
     * @param  array<string, mixed>  $module
     */
    private function registerObservers(array $module): void
    {
        foreach ($module['observers'] as $observer => $model) {
            $model::observe($observer);
        }
    }

    /**
     * Every module command is handed to Artisan in one `starting` callback:
     * registering a closure per command file meant dozens of callbacks that all
     * ran on every console boot for the same result.
     *
     * @param  array<int, class-string>  $classes
     */
    private function registerCommands(array $classes): void
    {
        if ($classes === []) {
            return;
        }

        Artisan::starting(static function ($artisan) use ($classes): void {
            $artisan->resolveCommands($classes);
        });
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
