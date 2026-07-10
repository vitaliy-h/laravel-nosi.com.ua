<?php

use App\Modules\ShopBasket\Http\Controllers\Web\AjaxBasketController;
use App\Modules\ShopBasket\Http\Controllers\Web\IndexController;

Route::get('/', [IndexController::class, 'index'])->name('basket.index');
Route::get('/checkout', [IndexController::class, 'checkout'])->name('basket.checkout');
Route::get('/{uuid}', [IndexController::class, 'show'])->name('basket.show');

Route::post('/change', [AjaxBasketController::class, 'change'])->name('change_product_count');
