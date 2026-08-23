<?php

use App\Modules\Subscribers\Http\Controllers\Web\SubscriberController;

Route::post('/', [SubscriberController::class, 'store'])->name('subscribers.store');
