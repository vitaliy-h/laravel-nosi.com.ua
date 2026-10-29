<?php

namespace App\Modules\Notification\Jobs;

use App\Modules\Notification\Enums\LogTelegramStatusEnum;
use App\Modules\Notification\Models\LogTelegram;
use App\Modules\Notification\Services\TelegramService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class SendTelegramMessageJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [30, 120];

    public function __construct(
        public LogTelegram $logTelegram,
    ) {}

    /**
     * A refusal is rethrown so the queue retries; the log keeps the last error
     * meanwhile and turns FAILED once the tries run out (failed()).
     */
    public function handle(TelegramService $telegram): void
    {
        try {
            $telegram->send($this->logTelegram->receiver, (string) $this->logTelegram->body);
        } catch (Throwable $e) {
            $this->logTelegram->update(['error_message' => $e->getMessage()]);

            throw $e;
        }

        $this->logTelegram->update([
            'status'        => LogTelegramStatusEnum::SENT,
            'error_message' => null,
        ]);
    }

    public function failed(?Throwable $e): void
    {
        $this->logTelegram->update([
            'status'        => LogTelegramStatusEnum::FAILED,
            'error_message' => $e?->getMessage() ?? $this->logTelegram->error_message,
        ]);
    }
}
