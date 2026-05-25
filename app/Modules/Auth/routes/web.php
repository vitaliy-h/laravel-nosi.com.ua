<?php

Route::prefix('/auth')->middleware(['guest', 'page:' . \App\Modules\Core\Enums\PageModule::AUTH->value])->group(function () {
    Route::get('/', [\App\Modules\Auth\Http\Controllers\AuthController::class, 'index'])
        ->name('auth');
    Route::post('/login', [\App\Modules\Auth\Http\Controllers\AuthController::class, 'login'])
        ->name('login.store');
    Route::post('/register', [\App\Modules\Auth\Http\Controllers\AuthController::class, 'register'])
        ->name('register.store');
    Route::post('/password-recovery', [\App\Modules\Auth\Http\Controllers\AuthController::class, 'passwordRecovery'])
        ->middleware('throttle:5,1')
        ->name('password.recovery.store');
    Route::post('/password-reset', [\App\Modules\Auth\Http\Controllers\AuthController::class, 'passwordReset'])
        ->name('password.reset.store');
});
Route::post('/logout', [\App\Modules\Auth\Http\Controllers\AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');
