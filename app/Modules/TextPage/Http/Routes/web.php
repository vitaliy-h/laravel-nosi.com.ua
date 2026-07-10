<?php

use App\Modules\TextPage\Http\Controllers\Web\IndexController;

Route::fallback([IndexController::class, '__invoke']);
