<?php

use App\Modules\Shop\Http\Controllers\Web\IndexController;
use App\Modules\Shop\Http\Controllers\Web\ReviewController;

Route::get('/', [IndexController::class, 'index'])->name('shop.index');

// Must stay above the wildcard routes below, otherwise `/reviews` is matched
// as a category slug.
Route::post('/reviews', [ReviewController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('shop.review.store');
Route::get('/reviews', [ReviewController::class, 'list'])->name('shop.review.list');

Route::get('/{categorySlug}/{productSlugId}', [IndexController::class, 'product'])->name('shop.product');
Route::get('/{categorySlug}', [IndexController::class, 'category'])->name('shop.category');
