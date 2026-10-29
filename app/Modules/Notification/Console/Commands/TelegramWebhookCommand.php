<?php

namespace App\Modules\Notification\Console\Commands;

use App\Modules\Notification\Services\TelegramService;
use App\Modules\ShopOrder\Actions\SendNewOrderTelegramAction;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Throwable;

#[Signature('telegram:webhook
    {--url= : Webhook URL; defaults to /api/telegram/webhook on APP_URL}
    {--delete : Remove the webhook instead}
    {--check : Only show what the app sees: token, recipients, config cache, webhook}')]
#[Description('Register the Telegram bot webhook, so the bot answers /start with the chat ID')]
class TelegramWebhookCommand extends Command
{
    public function handle(TelegramService $telegram): int
    {
        if ($this->option('check')) {
            $this->check();
        }

        try {
            if ($this->option('delete')) {
                $telegram->deleteWebhook();
                $this->info('Webhook removed.');
            } elseif (!$this->option('check')) {
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

    /**
     * Lengths only — the output is shown in the admin panel.
     */
    private function check(): void
    {
        $length = fn (mixed $value) => is_string($value) && $value !== '' ? 'set, ' . strlen($value) . ' chars' : 'EMPTY';

        $this->line('Environment file: ' . app()->environmentFilePath()
            . (is_readable(app()->environmentFilePath()) ? '' : ' (NOT READABLE)'));
        $this->line('Config cached: ' . (app()->configurationIsCached() ? 'YES — ' . app()->getCachedConfigPath() : 'no'));

        foreach (['TELEGRAM_BOT_TOKEN', 'TELEGRAM_CHAT_ID'] as $key) {
            $this->line("{$key}: env() " . $length(env($key))
                . ' | $_ENV ' . $length($_ENV[$key] ?? null)
                . ' | $_SERVER ' . $length($_SERVER[$key] ?? null)
                . ' | getenv ' . $length(getenv($key) ?: null));
        }

        $this->line('config services.telegram_bot.bot_token: ' . $length(config('services.telegram_bot.bot_token')));
        $this->line('config services.telegram_bot.chat_id: ' . $length(config('services.telegram_bot.chat_id')));

        try {
            $this->line('Recipients found: ' . count(app(SendNewOrderTelegramAction::class)->chatIds()));
        } catch (Throwable $e) {
            $this->line('Recipients: ' . $e->getMessage());
        }

        if (function_exists('opcache_get_status') && ($status = @opcache_get_status(false))) {
            $this->line('OPcache: on, validate_timestamps=' . var_export((bool) ini_get('opcache.validate_timestamps'), true)
                . ', revalidate_freq=' . ini_get('opcache.revalidate_freq'));
        }
    }
}
