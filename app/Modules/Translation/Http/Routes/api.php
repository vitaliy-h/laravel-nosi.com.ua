<?php

use App\Modules\Translation\Http\Controllers\Api\TranslationController;

Route::prefix('translations')->group(function () {

    Route::get('/', [TranslationController::class, 'index']);

    Route::get('/{group}', [TranslationController::class, 'group']);
});
