<?php

use App\Modules\Shop\Http\Controllers\Web\IndexController;
use Illuminate\Support\Facades\Route;

Route::get('/shop', [IndexController::class, 'index'])
    ->name('shop.index');

Route::get('/shop/{categorySlug}', [IndexController::class, 'category'])
    ->name('shop.category');

Route::get('/shop/{categorySlug}/{productSlugId}', [IndexController::class, 'product'])
    ->name('shop.product');
