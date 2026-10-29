<?php

namespace App\Modules\Notification\Http\Controllers\Api;

use App\Modules\Core\Abstract\BaseApiController;
use App\Modules\Notification\Services\TelegramService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * POST /api/telegram/webhook — Telegram delivers here what people write to the
 * bot (registered by `php artisan telegram:webhook`). Whoever writes gets their
 * chat ID back, to paste into the admin panel as an order-notification recipient.
 *
 * The reply goes in the response body (Telegram runs it as a method call), so
 * answering needs no request of our own. The body is Telegram's format, not
 * the API envelope — hence no $this->success().
 */
class TelegramWebhookController extends BaseApiController
{
    public function __invoke(Request $request, TelegramService $telegram): JsonResponse
    {
        // No bot configured — the endpoint does not exist.
        abort_if(blank(config('services.telegram.bot_token')), 404);

        if (!hash_equals($telegram->webhookSecret(), (string) $request->header('X-Telegram-Bot-Api-Secret-Token'))) {
            return $this->error('Forbidden', status: 403);
        }

        $chatId = $request->input('message.chat.id');

        // Edits, joins and the like — acknowledged, not answered.
        if (!is_int($chatId) && !is_string($chatId)) {
            return response()->json();
        }

        return response()->json([
            'method'     => 'sendMessage',
            'chat_id'    => $chatId,
            'parse_mode' => 'HTML',
            'text'       => __('site.telegram_bot.chat_id', ['id' => e((string) $chatId)], config('app.locale')),
        ]);
    }
}
