<?php

namespace App\Modules\Notification\Jobs;

use App\Modules\Notification\Services\TelegramService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendTelegramMessageJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [30, 120];

    public function __construct(
        public string $chatId,
        public string $text,
    ) {}

    public function handle(TelegramService $telegram): void
    {
        $telegram->send($this->chatId, $this->text);
    }
}
