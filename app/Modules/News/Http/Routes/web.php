<?php

use App\Modules\News\Http\Controllers\Web\IndexController;

Route::get('/', [IndexController::class, 'index'])->name('news.index');
Route::get('/{slug}', [IndexController::class, 'show'])->name('news.show');
