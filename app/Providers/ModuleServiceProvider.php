<?php

namespace App\Providers;

use App\Modules\Core\Observers\LogObserver;
use App\Modules\Core\Traits\Models\CascadesDeletes;
use Illuminate\Console\Application as Artisan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider
{
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

            $webRoute = $module . '/routes/web.php';
            $apiRoute = $module . '/routes/api.php';

            if (File::exists($webRoute)) {
                Route::middleware('web')
                    ->group($webRoute);
            }

            if (File::exists($apiRoute)) {
                Route::prefix('api')
                    ->middleware('api')
                    ->group($apiRoute);
            }
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
