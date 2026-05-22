<?php
Route::prefix('/news')->middleware('page:' . \App\Modules\Core\Enums\PageModule::NEWS->value)->group(function () {
    Route::get('/', [\App\Modules\News\Http\Controllers\NewsController::class, 'index'])
        ->name('news');
    Route::get('/{slug}', [\App\Modules\News\Http\Controllers\NewsController::class, 'show'])
        ->name('news.show');
});
