<?php
Route::prefix('/feedback')->group(function () {
    Route::post('/', [\App\Modules\Feedback\Http\Controllers\FeedbackController::class, 'store'])
        ->name('feedback.store');
});
