<?php

use App\Modules\Feedback\Http\Controllers\Web\FeedbackController;
use App\Modules\Feedback\Http\Controllers\Web\IndexController;

Route::get('/', [IndexController::class, 'index'])->name('feedback.index');
Route::post('/', [FeedbackController::class, 'store'])->name('feedback.store');
