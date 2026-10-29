<?php

namespace App\Modules\Notification\Services;

use App\Modules\Notification\Enums\LogTelegramStatusEnum;
use App\Modules\Notification\Enums\NotificationSourceEnum;
use App\Modules\Notification\Jobs\SendTelegramMessageJob;
use App\Modules\Notification\Models\LogTelegram;
use App\Modules\Notification\Support\NotificationSourceResolver;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class TelegramService
{
    private const string API_URL = 'https://api.telegram.org/bot%s/%s';

    /**
     * Logs the message (admin → Logs → Telegram) and leaves the sending to the
     * queue. HTML-formatted — callers escape user input with e().
     */
    public function queue(string $chatId, string $text, ?NotificationSourceEnum $source = null): LogTelegram
    {
        $log = LogTelegram::query()->create([
            'receiver' => $chatId,
            'body'     => $text,
            'status'   => LogTelegramStatusEnum::QUEUED,
            'source'   => $source ?? NotificationSourceResolver::resolve(),
        ]);

        SendTelegramMessageJob::dispatch($log);

        return $log;
    }

    /**
     * Sends an HTML-formatted message right away — callers escape user input with e().
     *
     * @throws RuntimeException when the bot token is missing or Telegram refuses the message
     */
    public function send(string $chatId, string $text): void
    {
        $this->call('sendMessage', [
            'chat_id'                  => $chatId,
            'text'                     => $text,
            'parse_mode'               => 'HTML',
            'disable_web_page_preview' => true,
        ]);
    }

    /**
     * Points the bot at the site: Telegram then POSTs every message to $url with
     * webhookSecret() in a header. While a webhook is set, getUpdates is refused.
     */
    public function setWebhook(string $url): void
    {
        $this->call('setWebhook', [
            'url'             => $url,
            'secret_token'    => $this->webhookSecret(),
            'allowed_updates' => ['message'],
        ]);
    }

    public function deleteWebhook(): void
    {
        $this->call('deleteWebhook');
    }

    /**
     * @return array<string, mixed>
     */
    public function webhookInfo(): array
    {
        return $this->call('getWebhookInfo');
    }

    /**
     * Derived from the bot token, so it needs no setting of its own and changes
     * with the token. Telegram allows only A-Z, a-z, 0-9, _ and - — hex fits.
     */
    public function webhookSecret(): string
    {
        return hash_hmac('sha256', 'telegram-webhook', $this->token());
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    private function call(string $method, array $params = []): array
    {
        $response = Http::timeout(10)->post(sprintf(self::API_URL, $this->token(), $method), $params);

        if (!$response->successful() || !$response->json('ok')) {
            $target = isset($params['chat_id']) ? " to {$params['chat_id']}" : '';

            throw new RuntimeException(
                "Telegram {$method}{$target} failed: " . ($response->json('description') ?? $response->status())
            );
        }

        return (array) $response->json('result');
    }

    private function token(): string
    {
        $token = (string) config('services.telegram_bot.bot_token');

        if ($token === '') {
            throw new RuntimeException('TELEGRAM_BOT_TOKEN is not set.');
        }

        return $token;
    }
}
