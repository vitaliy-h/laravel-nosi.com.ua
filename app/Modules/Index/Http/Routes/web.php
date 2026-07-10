<?php

use App\Modules\Index\Http\Controllers\Web\IndexController;

Route::get('/', [IndexController::class, 'index'])->name('index.index');
