<?php

use App\Modules\TextPage\Http\Controllers\TextPageController;
use Illuminate\Support\Facades\Route;


Route::get('/', [\App\Modules\Index\Http\Controllers\IndexController::class, 'index'])
    ->middleware('page:' . \App\Modules\Core\Enums\PageModule::INDEX->value)
    ->name('index');
Route::prefix('/feedback')->middleware('page:' . \App\Modules\Core\Enums\PageModule::FEEDBACK->value)->group(function () {
    Route::get('/', [\App\Modules\Feedback\Http\Controllers\FeedbackController::class, 'index'])
        ->name('feedback');
    Route::post('/', [\App\Modules\Feedback\Http\Controllers\FeedbackController::class, 'save'])
        ->name('feedback.save');
});
Route::prefix('/news')->middleware('page:' . \App\Modules\Core\Enums\PageModule::NEWS->value)->group(function () {
    Route::get('/', [\App\Modules\News\Http\Controllers\NewsController::class, 'index'])
        ->name('news');
    Route::get('/{slug}', [\App\Modules\News\Http\Controllers\NewsController::class, 'show'])
        ->name('news.show');
});



Route::get('/shop', [\App\Modules\Shop\Http\Controllers\ShopController::class, 'index'])
    ->middleware('page:' . \App\Modules\Core\Enums\PageModule::SHOP->value)
    ->name('shop');
Route::get('/shop/{categorySlug}', [\App\Modules\Shop\Http\Controllers\ShopController::class, 'category'])
    ->middleware('page:' . \App\Modules\Core\Enums\PageModule::SHOP->value)
    ->name('shop.category');

Route::get('/shop/{categorySlug}/{productSlug}', [\App\Modules\Shop\Http\Controllers\ShopController::class, 'product'])
    ->middleware('page:' . \App\Modules\Core\Enums\PageModule::SHOP->value)
    ->name('shop.product');

/**/


Route::get('{slug}', [TextPageController::class, 'index'])
    ->where('slug', '.*')
    ->middleware('page');
