<?php

use App\Modules\PageText\Http\Controllers\Web\IndexController;

Route::fallback([IndexController::class, '__invoke']);
