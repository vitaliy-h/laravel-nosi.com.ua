<?php

use App\Modules\Shop\Http\Controllers\Web\IndexController;

Route::get('/', [IndexController::class, 'index'])->name('shop.index');
Route::get('/{categorySlug}/{productSlugId}', [IndexController::class, 'product'])->name('shop.product');
Route::get('/{categorySlug}', [IndexController::class, 'category'])->name('shop.category');
