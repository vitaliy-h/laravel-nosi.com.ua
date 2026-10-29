<?php

namespace Tests\Unit\Notification;

use App\Modules\Notification\Enums\LogTelegramStatusEnum;
use App\Modules\Notification\Jobs\SendTelegramMessageJob;
use App\Modules\Notification\Services\TelegramService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use RuntimeException;
use Tests\TestCase;

class SendTelegramMessageJobTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.telegram.bot_token' => 'test-token']);
    }

    public function test_queue_logs_the_message_before_sending(): void
    {
        Queue::fake();

        $log = app(TelegramService::class)->queue('123', 'Hi');

        $this->assertSame(LogTelegramStatusEnum::QUEUED, $log->status);
        $this->assertSame('123', $log->receiver);
        Queue::assertPushed(SendTelegramMessageJob::class, fn ($job) => $job->logTelegram->is($log));
    }

    public function test_a_delivered_message_is_marked_sent(): void
    {
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true, 'result' => []])]);
        Queue::fake();
        $log = app(TelegramService::class)->queue('123', 'Hi');

        (new SendTelegramMessageJob($log))->handle(app(TelegramService::class));

        $this->assertSame(LogTelegramStatusEnum::SENT, $log->fresh()->status);
    }

    public function test_a_refusal_keeps_the_error_and_fails_after_the_last_try(): void
    {
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => false, 'description' => 'chat not found'], 400)]);
        Queue::fake();
        $log = app(TelegramService::class)->queue('123', 'Hi');
        $job = new SendTelegramMessageJob($log);

        try {
            $job->handle(app(TelegramService::class));
            $this->fail('The refusal must be rethrown for the queue to retry.');
        } catch (RuntimeException $e) {
            $job->failed($e);
        }

        $log->refresh();
        $this->assertSame(LogTelegramStatusEnum::FAILED, $log->status);
        $this->assertStringContainsString('chat not found', $log->error_message);
    }
}
