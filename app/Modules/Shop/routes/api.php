<?php

Route::prefix('/shop')
    ->group(function () {
        Route::get('/categories', [\App\Modules\Shop\Http\Controllers\Api\ShopController::class, 'categories'])
            ->name('api.shop.categories');
        Route::get('/categories/{slug}', [\App\Modules\Shop\Http\Controllers\Api\ShopController::class, 'category'])
            ->name('api.shop.category');
        Route::get('/categories/{slug}/products', [\App\Modules\Shop\Http\Controllers\Api\ShopController::class, 'productsByCategory'])
            ->name('api.shop.products_by_category');
        Route::get('/categories/{categorySlug}/products/{productSlug}', [\App\Modules\Shop\Http\Controllers\Api\ShopController::class, 'product'])
            ->name('api.shop.product');
    });
