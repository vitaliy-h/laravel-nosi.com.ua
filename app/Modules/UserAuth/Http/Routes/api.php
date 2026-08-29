<?php

use App\Modules\UserAuth\Http\Controllers\Api\ApiAuthController;

Route::prefix('/auth')->group(function () {
    Route::post('/login', [ApiAuthController::class, 'login'])
        ->name('api.login');
    Route::post('/register', [ApiAuthController::class, 'register'])
        ->name('api.register');

    Route::post('/logout', [ApiAuthController::class, 'logout'])
        ->middleware('auth:sanctum')
        ->name('api.logout');
});

