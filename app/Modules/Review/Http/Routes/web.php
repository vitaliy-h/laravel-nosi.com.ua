<?php

use App\Modules\Review\Http\Controllers\Web\ReviewController;

Route::post('/', [ReviewController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('review.store');
