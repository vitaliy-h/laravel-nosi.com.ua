<?php

namespace App\Providers;

use App\Filament\Generators\AdminResourceClassGenerator;
use App\Filament\Generators\AdminResourceManageRecordsPageClassGenerator;
use App\Modules\Core\Observers\LogObserver;
use App\Modules\Core\Services\LetterTemplateService;
use App\Modules\Core\Services\TranslationService;
use App\Modules\Core\Services\UrlHelper;
use Filament\Commands\FileGenerators\Resources\Pages\ResourceManageRecordsPageClassGenerator;
use Filament\Commands\FileGenerators\Resources\ResourceClassGenerator;
use Filament\Infolists\Components\TextEntry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Filament\Forms\Components\Field;
use Filament\Tables\Columns\Column;
use Filament\Tables\Filters\BaseFilter;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ResourceClassGenerator::class, AdminResourceClassGenerator::class);
        $this->app->bind(ResourceManageRecordsPageClassGenerator::class, AdminResourceManageRecordsPageClassGenerator::class);
        $this->app->singleton('url-helper', function () {
            return new UrlHelper();
        });
        $this->app->singleton('translation', function () {
            return new TranslationService();
        });
        $this->app->singleton('letter-template-service', function () {
            return new LetterTemplateService();
        });
    }

    //Automatic labels in forms, tables, pop-ups, etc.
    private function translateFilamentLabels(): void
    {
        $configure = function ($component) {
            $name = $component->getName();

            if (!$name) {
                return;
            }

            $name = str($name)->afterLast('.')->toString();

            $key = "admin.fields.$name";

            $translation = __($key);

            if ($translation === $key) {
                return;
            }

            $defaultLabel = (string)str($name)
                ->replace(['_', '-'], ' ')
                ->ucfirst();

            if ($component->getLabel() === $defaultLabel) {
                $component->label($translation);
            }
        };

        Field::configureUsing($configure);

        Column::configureUsing($configure);

        BaseFilter::configureUsing($configure);

        TextEntry::configureUsing($configure);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrap();

        $this->registerLogListeners();

        $this->translateFilamentLabels();

        $this->loadModuleRoutes();
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

        $dispatcher->listen('eloquent.deleted: *', function (
            string $event,
            array  $data
        ) {
            app(LogObserver::class)
                ->deleted($data[0]);
        });
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
}
