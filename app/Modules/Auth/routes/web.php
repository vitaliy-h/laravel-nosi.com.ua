<?php
Route::prefix('/login')->middleware(['guest', 'page:' . \App\Modules\Core\Enums\PageModule::LOGIN->value])->group(function () {
    Route::get('/', [\App\Modules\Auth\Http\Controllers\LoginController::class, 'index'])
        ->name('login');
    Route::post('/', [\App\Modules\Auth\Http\Controllers\LoginController::class, 'store'])
        ->name('login.store');
});
Route::post('/logout', [\App\Modules\Auth\Http\Controllers\LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');
Route::prefix('/registration')->middleware(['guest', 'page:' . \App\Modules\Core\Enums\PageModule::REGISTER->value])->group(function () {
    Route::get('/', [\App\Modules\Auth\Http\Controllers\RegisterController::class, 'index'])
        ->name('register');
    Route::post('/', [\App\Modules\Auth\Http\Controllers\RegisterController::class, 'store'])
        ->name('register.store');
});
Route::prefix('/password-recovery')->middleware(['guest', 'page:' . \App\Modules\Core\Enums\PageModule::PASSWORD_RECOVERY->value])->group(function () {
    Route::get('/', [\App\Modules\Auth\Http\Controllers\PasswordResetController::class, 'index'])
        ->name('password.recovery');
    Route::post('/', [\App\Modules\Auth\Http\Controllers\PasswordResetController::class, 'store'])
        ->name('password.recovery.store');
});
