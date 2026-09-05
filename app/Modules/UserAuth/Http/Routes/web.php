<?php

use App\Modules\UserAuth\Http\Controllers\Web\AuthController;
use App\Modules\UserAuth\Http\Controllers\Web\ImpersonationController;
use App\Modules\UserAuth\Http\Controllers\Web\IndexController;
use App\Modules\UserAuth\Http\Controllers\Web\OAuthController;

Route::middleware(['guest'])->group(function () {
    Route::get('/', [IndexController::class, 'index'])->name('auth.index');
    Route::post('/login', [AuthController::class, 'login'])
        ->name('login.store');
    Route::post('/register', [AuthController::class, 'register'])
        ->name('register.store');
    Route::post('/password-recovery', [AuthController::class, 'passwordRecovery'])
        ->middleware('throttle:5,1')
        ->name('password.recovery.store');
    Route::post('/password-reset', [AuthController::class, 'passwordReset'])
        ->name('password.reset.store');
});
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::get('/impersonate/{user}', [ImpersonationController::class, 'impersonate'])
    ->name('impersonate')
    ->middleware('signed');

Route::post('/impersonation/leave', [ImpersonationController::class, 'leave'])
    ->name('impersonate.leave')
    ->middleware('auth');

Route::prefix('/social')->group(function () {
    Route::get('/{provider}', [OAuthController::class, 'redirect'])
        ->name('social.redirect');
    Route::get('/{provider}/callback', [OAuthController::class, 'callback'])
        ->name('social.callback');
});
