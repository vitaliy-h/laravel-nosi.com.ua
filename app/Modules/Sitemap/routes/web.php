<?php

Route::get('/sitemap.xml', [\App\Modules\Sitemap\Http\Controllers\SitemapController::class, 'index']);
Route::get('/sitemap-base.xml', [\App\Modules\Sitemap\Http\Controllers\SitemapController::class, 'base']);
Route::get('/sitemap-news-{page}.xml', [\App\Modules\Sitemap\Http\Controllers\SitemapController::class, 'news'])
    ->where('page', '[1-9][0-9]*');
Route::get('/sitemap-categories-{page}.xml', [\App\Modules\Sitemap\Http\Controllers\SitemapController::class, 'categories'])
    ->where('page', '[1-9][0-9]*');
Route::get('/sitemap-products-{page}.xml', [\App\Modules\Sitemap\Http\Controllers\SitemapController::class, 'products'])
    ->where('page', '[1-9][0-9]*');
