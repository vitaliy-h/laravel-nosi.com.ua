<?php
Route::get('/', [\App\Modules\Index\Http\Controllers\IndexController::class, 'index'])
    ->middleware('page:' . \App\Modules\Core\Enums\PageModule::INDEX->value)
    ->name('index');
