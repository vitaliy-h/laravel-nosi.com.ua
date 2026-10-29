<?php

namespace Tests\Unit\Notification;

use App\Modules\Notification\Services\TelegramService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class TelegramServiceTest extends TestCase
{
    /**
     * AppServiceProvider overwrites services.{social provider} — `services.telegram`
     * among them — with the social login config at boot. The bot's own settings
     * must live under a key it does not touch, or the token reads as empty.
     */
    public function test_the_bot_config_survives_boot(): void
    {
        $this->assertArrayHasKey('bot_token', config('services.telegram_bot'));
        $this->assertArrayHasKey('chat_id', config('services.telegram_bot'));
    }

    public function test_posts_the_message_to_the_bot_api(): void
    {
        config(['services.telegram_bot.bot_token' => 'test-token']);
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true])]);

        app(TelegramService::class)->send('123', '<b>Hi</b>');

        Http::assertSent(fn (Request $request) => $request->url() === 'https://api.telegram.org/bottest-token/sendMessage'
            && $request['chat_id'] === '123'
            && $request['text'] === '<b>Hi</b>'
            && $request['parse_mode'] === 'HTML');
    }

    public function test_a_refused_message_throws_so_the_job_retries(): void
    {
        config(['services.telegram_bot.bot_token' => 'test-token']);
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => false, 'description' => 'chat not found'], 400)]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('chat not found');

        app(TelegramService::class)->send('123', 'Hi');
    }

    public function test_the_command_registers_the_webhook_with_the_secret(): void
    {
        config(['services.telegram_bot.bot_token' => 'test-token']);
        Http::fake([
            'api.telegram.org/bottest-token/setWebhook'     => Http::response(['ok' => true, 'result' => true]),
            'api.telegram.org/bottest-token/getWebhookInfo' => Http::response(['ok' => true, 'result' => ['url' => 'https://x.test/api/telegram/webhook']]),
        ]);

        $this->artisan('telegram:webhook', ['--url' => 'https://x.test/api/telegram/webhook'])
            ->expectsOutput('Webhook set to https://x.test/api/telegram/webhook')
            ->assertSuccessful();

        Http::assertSent(fn (Request $request) => str_ends_with($request->url(), '/setWebhook')
            && $request['url'] === 'https://x.test/api/telegram/webhook'
            && $request['secret_token'] === app(TelegramService::class)->webhookSecret());
    }

    public function test_missing_token_throws(): void
    {
        config(['services.telegram_bot.bot_token' => null]);
        Http::fake();

        $this->expectException(RuntimeException::class);

        app(TelegramService::class)->send('123', 'Hi');

        Http::assertNothingSent();
    }
}
