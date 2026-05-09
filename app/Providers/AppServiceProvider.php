<?php

namespace App\Providers;

use App\Filament\Generators\AdminResourceClassGenerator;
use App\Filament\Generators\AdminResourceManageRecordsPageClassGenerator;
use App\Modules\Core\Services\TranslationService;
use App\Modules\Core\Services\UrlHelper;
use Filament\Commands\FileGenerators\Resources\Pages\ResourceManageRecordsPageClassGenerator;
use Filament\Commands\FileGenerators\Resources\ResourceClassGenerator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Filament\Forms\Components\Field;
use Filament\Tables\Columns\Column;

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

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrap();

        //Automatic labels in forms, tables, pop-ups, etc.
        Field::configureUsing(function (Field $component) {
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
            // EXACT Filament default label style
            $defaultLabel = (string) str($name)
                ->replace(['_', '-'], ' ')
                ->ucfirst();
            if ($component->getLabel() === $defaultLabel) {
                $component->label($translation);
            }
        });
    }
}
