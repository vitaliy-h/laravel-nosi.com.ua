<?php
Route::post('/order/place', [\App\Modules\Orders\Http\Controllers\Web\OrderController::class, 'placeOrder'])
    ->name('order.place');

Route::get('/order/{uuid}', [\App\Modules\Orders\Http\Controllers\Web\OrderController::class, 'show'])
    ->name('order.show');

Route::get('/order/{uuid}/success', [\App\Modules\Orders\Http\Controllers\Web\OrderController::class, 'success'])
    ->name('order.success');

Route::get('/order/{uuid}/error', [\App\Modules\Orders\Http\Controllers\Web\OrderController::class, 'error'])
    ->name('order.error');
