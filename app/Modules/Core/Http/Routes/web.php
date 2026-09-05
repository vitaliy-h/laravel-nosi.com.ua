<?php

use App\Modules\Core\Http\Controllers\ImagePreviewController;

Route::get('/storage/{preset}/{path}', [ImagePreviewController::class, '__invoke'])
    ->where('path', '.*');

// The global fallback route lives in PageText's web.php
