<?php

Route::fallback([\App\Modules\Core\Http\Controllers\ModuleRouteController::class, '__invoke'])
    ->where('slug', '.*');
