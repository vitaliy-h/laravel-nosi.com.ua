<?php
Route::prefix('/feedback')->middleware('page:' . \App\Modules\Core\Enums\PageModule::FEEDBACK->value)->group(function () {
    Route::get('/', [\App\Modules\Feedback\Http\Controllers\FeedbackController::class, 'index'])
        ->name('feedback');
    Route::post('/', [\App\Modules\Feedback\Http\Controllers\FeedbackController::class, 'store'])
        ->name('feedback.store');
});
