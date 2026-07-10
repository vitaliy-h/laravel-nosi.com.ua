<?php

use App\Modules\Shop\Http\Controllers\Api\ShopController;

Route::prefix('/shop')
    ->group(function () {
        Route::get('/categories', [ShopController::class, 'categories'])
            ->name('api.shop.categories');
        Route::get('/categories/{id}', [ShopController::class, 'category'])
            ->name('api.shop.category');
        Route::get('/products', [ShopController::class, 'products'])
            ->name('api.shop.products');
        Route::get('/products/{id}', [ShopController::class, 'product'])
            ->name('api.shop.product');
    });
