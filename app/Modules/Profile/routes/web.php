<?php
Route::prefix('/profile')->middleware('auth')->group(function () {
    Route::post('/update-user-data', [\App\Modules\Profile\Http\Controllers\ProfileController::class, 'updateUserData'])
        ->name('profile.update.user.data');
    Route::post('/update-user-password', [\App\Modules\Profile\Http\Controllers\ProfileController::class, 'updateUserPassword'])
        ->name('profile.update.user.password');
    Route::post('/delete-user-account', [\App\Modules\Profile\Http\Controllers\ProfileController::class, 'deleteUserAccount'])
        ->name('profile.delete.user.account');
});
