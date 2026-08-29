<?php

use App\Modules\UserAuth\Http\Controllers\Web\IndexController;

Route::middleware(['guest'])->group(function () {
    Route::get('/', [IndexController::class, 'index'])->name('auth.index');
    Route::post('/login', [\App\Modules\UserAuth\Http\Controllers\Web\AuthController::class, 'login'])
        ->name('login.store');
    Route::post('/register', [\App\Modules\UserAuth\Http\Controllers\Web\AuthController::class, 'register'])
        ->name('register.store');
    Route::post('/password-recovery', [\App\Modules\UserAuth\Http\Controllers\Web\AuthController::class, 'passwordRecovery'])
        ->middleware('throttle:5,1')
        ->name('password.recovery.store');
    Route::post('/password-reset', [\App\Modules\UserAuth\Http\Controllers\Web\AuthController::class, 'passwordReset'])
        ->name('password.reset.store');
});
Route::post('/logout', [\App\Modules\UserAuth\Http\Controllers\Web\AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::get('/impersonate/{user}', [\App\Modules\UserAuth\Http\Controllers\Web\ImpersonationController::class, 'impersonate'])
    ->name('impersonate')
    ->middleware('signed');

Route::post('/impersonation/leave', [\App\Modules\UserAuth\Http\Controllers\Web\ImpersonationController::class, 'leave'])
    ->name('impersonate.leave')
    ->middleware('auth');

Route::prefix('/social')->group(function () {
    Route::get('/{provider}', [\App\Modules\UserAuth\Http\Controllers\Web\OAuthController::class, 'redirect'])
        ->name('social.redirect');
    Route::get('/{provider}/callback', [\App\Modules\UserAuth\Http\Controllers\Web\OAuthController::class, 'callback'])
        ->name('social.callback');
});
