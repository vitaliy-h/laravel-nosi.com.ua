<?php

namespace App\Providers;

use App\Filament\Generators\AdminResourceClassGenerator;
use App\Filament\Generators\AdminResourceManageRecordsPageClassGenerator;
use App\Modules\Core\Services\UrlHelper;
use Filament\Commands\FileGenerators\Resources\Pages\ResourceManageRecordsPageClassGenerator;
use Filament\Commands\FileGenerators\Resources\ResourceClassGenerator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrap();
    }
}
