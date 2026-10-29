<?php

namespace Tests\Unit\Notification;

use App\Modules\Notification\Services\TelegramService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class TelegramServiceTest extends TestCase
{
    public function test_posts_the_message_to_the_bot_api(): void
    {
        config(['services.telegram.bot_token' => 'test-token']);
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true])]);

        app(TelegramService::class)->send('123', '<b>Hi</b>');

        Http::assertSent(fn (Request $request) => $request->url() === 'https://api.telegram.org/bottest-token/sendMessage'
            && $request['chat_id'] === '123'
            && $request['text'] === '<b>Hi</b>'
            && $request['parse_mode'] === 'HTML');
    }

    public function test_a_refused_message_throws_so_the_job_retries(): void
    {
        config(['services.telegram.bot_token' => 'test-token']);
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => false, 'description' => 'chat not found'], 400)]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('chat not found');

        app(TelegramService::class)->send('123', 'Hi');
    }

    public function test_missing_token_throws(): void
    {
        config(['services.telegram.bot_token' => null]);
        Http::fake();

        $this->expectException(RuntimeException::class);

        app(TelegramService::class)->send('123', 'Hi');

        Http::assertNothingSent();
    }
}
