<?php

use App\Modules\Profile\Http\Controllers\Web\IndexController;

Route::middleware('auth')->group(function () {
    Route::get('/', [IndexController::class, 'index'])->name('profile.index');
    Route::get('/edit', [IndexController::class, 'edit'])->name('profile.edit');
    Route::get('/orders', [IndexController::class, 'orders'])->name('profile.orders');
    Route::get('/orders/{id}', [IndexController::class, 'order'])->name('profile.orders.show');

    Route::post('/update-user-data', [\App\Modules\Profile\Http\Controllers\Web\ProfileController::class, 'updateUserData'])
        ->name('profile.update.user.data');
    Route::post('/update-user-password', [\App\Modules\Profile\Http\Controllers\Web\ProfileController::class, 'updateUserPassword'])
        ->name('profile.update.user.password');
    Route::post('/delete-user-account', [\App\Modules\Profile\Http\Controllers\Web\ProfileController::class, 'deleteUserAccount'])
        ->name('profile.delete.user.account');
    Route::post('/social/{provider}/disconnect', [\App\Modules\Profile\Http\Controllers\Web\SocialProfileController::class, 'disconnect'])
        ->name('profile.social.disconnect');
});
