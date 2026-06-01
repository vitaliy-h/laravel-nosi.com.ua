<?php

use App\Modules\_debug\Http\Controllers\Web\ApiResponseController;
use Spatie\Permission\Middleware\RoleMiddleware;

Route::prefix('/_debug')
    ->middleware(['auth', RoleMiddleware::using('admin')])
    ->group(function () {
        Route::get('/api_response', [ApiResponseController::class, 'api_response_index'])->name('test.index');
        Route::get('/api_response/{action}', [ApiResponseController::class, 'api_response_request'])->name('test.api.response');
    });
