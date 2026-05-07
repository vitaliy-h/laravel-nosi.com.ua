<?php

use App\Modules\TextPage\Http\Controllers\TextPageController;
use Illuminate\Support\Facades\Route;


Route::get('/', [\App\Modules\Index\Http\Controllers\IndexController::class, 'index'])
    ->middleware('page:' . \App\Modules\Core\Enums\PageModule::INDEX->value)
    ->name('index');
Route::get('/feedback', [\App\Modules\Feedback\Http\Controllers\FeedbackController::class, 'index'])
    ->middleware('page:' . \App\Modules\Core\Enums\PageModule::FEEDBACK->value)
    ->name('feedback');
Route::post('/feedback', [\App\Modules\Feedback\Http\Controllers\FeedbackController::class, 'save'])
    ->middleware('page:' . \App\Modules\Core\Enums\PageModule::FEEDBACK->value)
    ->name('feedback.save');
Route::get('/news', [\App\Modules\News\Http\Controllers\NewsController::class, 'index'])
    ->middleware('page:' . \App\Modules\Core\Enums\PageModule::NEWS->value)
    ->name('news');
Route::get('/news/{slug}', [\App\Modules\News\Http\Controllers\NewsController::class, 'show'])
    ->middleware('page:' . \App\Modules\Core\Enums\PageModule::NEWS->value)
    ->name('news.show');

Route::get('{slug}', [TextPageController::class,'index'])
    ->middleware('page');
