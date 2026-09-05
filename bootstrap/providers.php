<?php

use App\Providers\AppServiceProvider;
use App\Providers\Filament\AdminPanelProvider;
use App\Providers\FilamentServiceProvider;
use App\Providers\ModuleServiceProvider;

return [
    AppServiceProvider::class,
    FilamentServiceProvider::class,
    AdminPanelProvider::class,
    ModuleServiceProvider::class,
];
