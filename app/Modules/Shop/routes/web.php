<?php
Route::get('/shop', [\App\Modules\Shop\Http\Controllers\ShopController::class, 'index'])
    ->middleware('page:' . \App\Modules\Core\Enums\PageModule::SHOP->value)
    ->name('shop');
Route::get('/shop/{categorySlug}', [\App\Modules\Shop\Http\Controllers\ShopController::class, 'category'])
    ->middleware('page:' . \App\Modules\Core\Enums\PageModule::SHOP->value)
    ->name('shop.category');

Route::get('/shop/{categorySlug}/{productSlug}', [\App\Modules\Shop\Http\Controllers\ShopController::class, 'product'])
    ->middleware('page:' . \App\Modules\Core\Enums\PageModule::SHOP->value)
    ->name('shop.product');

