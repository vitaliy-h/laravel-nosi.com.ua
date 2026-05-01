<?php

namespace App\Providers;

use App\Filament\Generators\AdminResourceClassGenerator;
use App\Filament\Generators\AdminResourceManageRecordsPageClassGenerator;
use Filament\Commands\FileGenerators\Resources\Pages\ResourceManageRecordsPageClassGenerator;
use Filament\Commands\FileGenerators\Resources\ResourceClassGenerator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ResourceClassGenerator::class, AdminResourceClassGenerator::class);
        $this->app->bind(ResourceManageRecordsPageClassGenerator::class, AdminResourceManageRecordsPageClassGenerator::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
