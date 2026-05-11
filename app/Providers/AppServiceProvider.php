<?php

namespace App\Providers;

use App\Filament\Generators\AdminResourceClassGenerator;
use App\Filament\Generators\AdminResourceManageRecordsPageClassGenerator;
use App\Modules\Core\Observers\ActivityLogObserver;
use App\Modules\Core\Services\TranslationService;
use App\Modules\Core\Services\UrlHelper;
use Filament\Commands\FileGenerators\Resources\Pages\ResourceManageRecordsPageClassGenerator;
use Filament\Commands\FileGenerators\Resources\ResourceClassGenerator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Filament\Forms\Components\Field;
use Filament\Tables\Columns\Column;
use Filament\Tables\Filters\BaseFilter;

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

            $defaultLabel = (string) str($name)
                ->replace(['_', '-'], ' ')
                ->ucfirst();

            if ($component->getLabel() === $defaultLabel) {
                $component->label($translation);
            }
        };

        Field::configureUsing($configure);

        Column::configureUsing($configure);

        BaseFilter::configureUsing($configure);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrap();

        $this->registerActivityLogListeners();

        $this->translateFilamentLabels();
    }

    private function registerActivityLogListeners(): void
    {
        $dispatcher = Model::getEventDispatcher();

        $dispatcher->listen('eloquent.created: *', function (
            string $event,
            array $data
        ) {
            app(ActivityLogObserver::class)
                ->created($data[0]);
        });

        $dispatcher->listen('eloquent.updated: *', function (
            string $event,
            array $data
        ) {
            app(ActivityLogObserver::class)
                ->updated($data[0]);
        });

        $dispatcher->listen('eloquent.deleted: *', function (
            string $event,
            array $data
        ) {
            app(ActivityLogObserver::class)
                ->deleted($data[0]);
        });
    }
}
