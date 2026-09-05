<?php

use App\Modules\Sitemap\Http\Controllers\SitemapController;

Route::get('/sitemap.xml', [SitemapController::class, 'index']);
Route::get('/sitemap-base.xml', [SitemapController::class, 'base']);
Route::get('/sitemap-news-{page}.xml', [SitemapController::class, 'news'])
    ->where('page', '[1-9][0-9]*');
Route::get('/sitemap-categories-{page}.xml', [SitemapController::class, 'categories'])
    ->where('page', '[1-9][0-9]*');
Route::get('/sitemap-products-{page}.xml', [SitemapController::class, 'products'])
    ->where('page', '[1-9][0-9]*');
