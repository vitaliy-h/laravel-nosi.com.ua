<?php
//\App\Modules\Core\Http\Controllers\PageRoutesController::register();

Route::get('/storage/{preset}/{path}', [\App\Modules\Core\Http\Controllers\ImagePreviewController::class, '__invoke'])
    ->where('path', '.*');

Route::get('/sitemap.xml', [\App\Modules\Core\Http\Controllers\SitemapController::class, 'index']);
Route::get('/sitemap-base.xml', [\App\Modules\Core\Http\Controllers\SitemapController::class, 'base']);
Route::get('/sitemap-news-{page}.xml', [\App\Modules\Core\Http\Controllers\SitemapController::class, 'news'])
    ->where('page', '[1-9][0-9]*');
Route::get('/sitemap-categories-{page}.xml', [\App\Modules\Core\Http\Controllers\SitemapController::class, 'categories'])
    ->where('page', '[1-9][0-9]*');
Route::get('/sitemap-products-{page}.xml', [\App\Modules\Core\Http\Controllers\SitemapController::class, 'products'])
    ->where('page', '[1-9][0-9]*');

Route::fallback([\App\Modules\Core\Http\Controllers\ModuleRouteController::class, '__invoke'])
    ->where('slug', '.*');
