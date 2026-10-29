<?php

namespace App\Modules\Notification\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class TelegramService
{
    private const string API_URL = 'https://api.telegram.org/bot%s/sendMessage';

    /**
     * Sends an HTML-formatted message — callers escape user input with e().
     *
     * @throws RuntimeException when the bot token is missing or Telegram refuses the message
     */
    public function send(string $chatId, string $text): void
    {
        $token = config('services.telegram.bot_token');

        if (blank($token)) {
            throw new RuntimeException('TELEGRAM_BOT_TOKEN is not set.');
        }

        $response = Http::timeout(10)->post(sprintf(self::API_URL, $token), [
            'chat_id'                  => $chatId,
            'text'                     => $text,
            'parse_mode'               => 'HTML',
            'disable_web_page_preview' => true,
        ]);

        if (!$response->successful() || !$response->json('ok')) {
            throw new RuntimeException(
                "Telegram sendMessage to {$chatId} failed: " . ($response->json('description') ?? $response->status())
            );
        }
    }
}
