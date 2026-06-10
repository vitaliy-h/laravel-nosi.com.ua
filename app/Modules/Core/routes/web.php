<?php
\App\Modules\Core\Http\Controllers\PageRoutesController::register();

Route::fallback([\App\Modules\Core\Http\Controllers\ModuleRouteController::class, '__invoke'])
    ->where('slug', '.*');
