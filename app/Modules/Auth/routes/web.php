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

Route::get('/auth/impersonate/{user}', [\App\Modules\Auth\Http\Controllers\Web\ImpersonationController::class, 'impersonate'])
    ->name('impersonate')
    ->middleware('signed');

Route::post('/auth/impersonation/leave', [\App\Modules\Auth\Http\Controllers\Web\ImpersonationController::class, 'leave'])
    ->name('impersonate.leave')
    ->middleware('auth');

Route::prefix('/auth/social')->group(function () {
    Route::get('/{provider}', [\App\Modules\Auth\Http\Controllers\Web\OAuthController::class, 'redirect'])
        ->name('social.redirect');
    Route::get('/{provider}/callback', [\App\Modules\Auth\Http\Controllers\Web\OAuthController::class, 'callback'])
        ->name('social.callback');
});
