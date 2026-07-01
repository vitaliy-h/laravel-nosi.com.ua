<?php
Route::post('/basket/change', [App\Modules\Basket\Http\Controllers\Web\IndexController::class, 'ajax_basket_change'])->name('change_product_count');
