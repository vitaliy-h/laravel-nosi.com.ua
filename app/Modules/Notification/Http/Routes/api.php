<?php

use App\Modules\Notification\Http\Controllers\Api\TelegramWebhookController;

// The secret header is the protection; Telegram retries a failed delivery, so
// no throttle either.
Route::post('/telegram/webhook', TelegramWebhookController::class)
    ->name('api.telegram.webhook');
