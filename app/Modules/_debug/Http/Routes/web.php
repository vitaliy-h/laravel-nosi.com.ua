<?php

use App\Modules\_debug\Http\Controllers\Web\AbcResponseController;
use Spatie\Permission\Middleware\RoleMiddleware;

Route::middleware(['auth', RoleMiddleware::using('super_admin')])
    ->group(function () {
        Route::get('/abc-response', [AbcResponseController::class, 'abc_response_index'])->name('test.index');
        Route::match(
            ['GET', 'POST'],
            '/abc-response/{action}',
            [AbcResponseController::class, 'abc_response_request']
        )->name('test.abc.response');
    });
