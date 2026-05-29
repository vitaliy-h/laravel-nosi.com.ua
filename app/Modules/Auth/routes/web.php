<?php

Route::prefix('/auth')->middleware(['guest'])->group(function () {
    Route::post('/login', [\App\Modules\Auth\Http\Controllers\Web\AuthController::class, 'login'])
        ->name('login.store');
    Route::post('/register', [\App\Modules\Auth\Http\Controllers\Web\AuthController::class, 'register'])
        ->name('register.store');
    Route::post('/password-recovery', [\App\Modules\Auth\Http\Controllers\Web\AuthController::class, 'passwordRecovery'])
        ->middleware('throttle:5,1')
        ->name('password.recovery.store');
    Route::post('/password-reset', [\App\Modules\Auth\Http\Controllers\Web\AuthController::class, 'passwordReset'])
        ->name('password.reset.store');
});
Route::post('/auth/logout', [\App\Modules\Auth\Http\Controllers\Web\AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');
