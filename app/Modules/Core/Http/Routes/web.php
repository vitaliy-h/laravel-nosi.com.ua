<?php

Route::get('/storage/{preset}/{path}', [\App\Modules\Core\Http\Controllers\ImagePreviewController::class, '__invoke'])
    ->where('path', '.*');

// The global fallback route lives in PageText's web.php
