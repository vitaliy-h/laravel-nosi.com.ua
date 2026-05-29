<?php
Route::prefix('/feedback')->group(function () {
    Route::post('/', [\App\Modules\Feedback\Http\Controllers\Web\FeedbackController::class, 'store'])
        ->name('feedback.store');
});
