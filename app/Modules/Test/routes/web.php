<?php

use App\Modules\Test\Http\Controllers\Web\TestController;
use Spatie\Permission\Middleware\RoleMiddleware;

Route::prefix('/test')
    ->middleware(['auth', RoleMiddleware::using('admin')])
    ->group(function () {
        Route::get('/api_response', [TestController::class, 'api_response_index'])->name('test.index');
        Route::get('/api_response/{action}', [TestController::class, 'api_response_request'])->name('test.api.response');
    });
