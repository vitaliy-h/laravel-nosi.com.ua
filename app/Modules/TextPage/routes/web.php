<?php

use App\Modules\TextPage\Http\Controllers\TextPageController;

Route::fallback([TextPageController::class, 'index'])
    ->where('slug', '.*')
    ->middleware('page');
