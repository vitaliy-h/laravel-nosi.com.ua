<?php

use App\Modules\Shop\Http\Controllers\Web\IndexController;
use App\Modules\Shop\Http\Controllers\Web\ProductReviewController;

Route::get('/', [IndexController::class, 'index'])->name('shop.index');

// Must stay above the wildcard routes below, otherwise `/product-reviews` is matched
// as a category slug.
Route::post('/product-reviews', [ProductReviewController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('shop.product.review.store');
Route::get('/product-reviews', [ProductReviewController::class, 'list'])->name('shop.product.review.list');

Route::get('/{categorySlug}/{productSlugId}', [IndexController::class, 'product'])->name('shop.product');
Route::get('/{categorySlug}', [IndexController::class, 'category'])->name('shop.category');
