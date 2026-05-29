<?php
Route::prefix('/profile')
    ->middleware('auth:sanctum')
    ->group(function () {
        Route::post('/update-user-data', [\App\Modules\Profile\Http\Controllers\Api\ProfileController::class, 'updateUserData'])
            ->name('api.profile.update.user.data');
        Route::post('/update-user-password', [\App\Modules\Profile\Http\Controllers\Api\ProfileController::class, 'updateUserPassword'])
            ->name('api.profile.update.user.password');
        Route::post('/delete-user-account', [\App\Modules\Profile\Http\Controllers\Api\ProfileController::class, 'deleteUserAccount'])
            ->name('api.profile.delete.user.account');
    });
