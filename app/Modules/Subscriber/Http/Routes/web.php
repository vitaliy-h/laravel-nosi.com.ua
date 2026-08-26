<?php

use App\Modules\Subscriber\Http\Controllers\Web\SubscriberController;

Route::post('/', [SubscriberController::class, 'store'])->name('subscriber.store');
