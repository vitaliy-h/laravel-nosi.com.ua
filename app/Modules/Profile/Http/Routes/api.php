<?php

use App\Modules\Profile\Http\Controllers\Api\ProfileController;

Route::prefix('/profile')
    ->middleware('auth:sanctum')
    ->group(function () {
        Route::post('/update-user-data', [ProfileController::class, 'updateUserData'])
            ->name('api.profile.update.user.data');
        Route::post('/update-user-password', [ProfileController::class, 'updateUserPassword'])
            ->name('api.profile.update.user.password');
        Route::post('/delete-user-account', [ProfileController::class, 'deleteUserAccount'])
            ->name('api.profile.delete.user.account');
    });
