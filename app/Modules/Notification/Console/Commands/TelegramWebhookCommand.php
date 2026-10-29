<?php

namespace App\Modules\Notification\Console\Commands;

use App\Modules\Notification\Services\TelegramService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Throwable;

#[Signature('telegram:webhook
    {--url= : Webhook URL; defaults to /api/telegram/webhook on APP_URL}
    {--delete : Remove the webhook instead}')]
#[Description('Register the Telegram bot webhook, so the bot answers /start with the chat ID')]
class TelegramWebhookCommand extends Command
{
    public function handle(TelegramService $telegram): int
    {
        try {
            if ($this->option('delete')) {
                $telegram->deleteWebhook();
                $this->info('Webhook removed.');
            } else {
                $url = $this->option('url') ?: route('api.telegram.webhook');
                $telegram->setWebhook($url);
                $this->info("Webhook set to {$url}");
            }

            $info = $telegram->webhookInfo();
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->line('Current URL: ' . ($info['url'] ?: '—'));
        $this->line('Pending updates: ' . ($info['pending_update_count'] ?? 0));

        if (!empty($info['last_error_message'])) {
            $this->warn('Last error: ' . $info['last_error_message']);
        }

        return self::SUCCESS;
    }
}
