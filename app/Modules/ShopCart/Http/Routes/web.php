<?php

use App\Modules\ShopCart\Http\Controllers\Web\AjaxCartController;
use App\Modules\ShopCart\Http\Controllers\Web\IndexController;
use App\Modules\ShopCart\Http\Controllers\Web\PromocodeController;

Route::get('/', [IndexController::class, 'index'])->name('cart.index');
Route::get('/checkout', [IndexController::class, 'checkout'])->name('cart.checkout');
Route::get('/{uuid}', [IndexController::class, 'show'])->name('cart.show');

Route::post('/change', [AjaxCartController::class, 'change'])->name('change_product_count');
Route::post('/promocode/apply', [PromocodeController::class, 'apply'])->name('promocode.apply');
Route::post('/promocode/remove', [PromocodeController::class, 'remove'])->name('promocode.remove');
