<?php

use App\Modules\ShopOrders\Http\Controllers\Web\IndexController;
use App\Modules\ShopOrders\Http\Controllers\Web\OrderController;

Route::post('/create', [OrderController::class, 'create'])->name('order.create');

Route::get('/{uuid}', [IndexController::class, 'show'])->name('order.show');
Route::get('/{uuid}/success', [IndexController::class, 'success'])->name('order.success');
Route::get('/{uuid}/error', [IndexController::class, 'error'])->name('order.error');
