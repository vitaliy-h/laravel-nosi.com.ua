<?php
//\App\Modules\Core\Http\Controllers\PageRoutesController::register();

Route::get('/storage/{preset}/{path}', [\App\Modules\Core\Http\Controllers\ImagePreviewController::class, '__invoke'])
    ->where('path', '.*');


Route::fallback([\App\Modules\Core\Http\Controllers\ModuleRouteController::class, '__invoke'])
    ->where('slug', '.*');
