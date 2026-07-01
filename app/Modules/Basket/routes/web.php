<?php
Route::post('/basket/change', [App\Modules\Basket\Http\Controllers\Web\AjaxBasketController::class, 'change'])->name('change_product_count');
