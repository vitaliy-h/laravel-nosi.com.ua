<?php

namespace Tests\Feature\Notification;

use App\Modules\Notification\Services\TelegramService;
use Tests\TestCase;

class TelegramWebhookTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['app.locale' => 'uk', 'services.telegram_bot.bot_token' => 'test-token']);
    }

    public function test_answers_with_the_chat_id(): void
    {
        $this->postJson('/api/telegram/webhook', $this->update(6572793), $this->secretHeader())
            ->assertOk()
            ->assertJson([
                'method'     => 'sendMessage',
                'chat_id'    => 6572793,
                'parse_mode' => 'HTML',
            ])
            ->assertJsonPath('text', fn (string $text) => str_contains($text, '<code>6572793</code>'));
    }

    public function test_a_wrong_secret_is_forbidden(): void
    {
        $this->postJson('/api/telegram/webhook', $this->update(1), ['X-Telegram-Bot-Api-Secret-Token' => 'nope'])
            ->assertForbidden();
    }

    public function test_without_a_bot_token_the_endpoint_does_not_exist(): void
    {
        config(['services.telegram_bot.bot_token' => null]);

        $this->postJson('/api/telegram/webhook', $this->update(1))->assertNotFound();
    }

    public function test_an_update_without_a_message_is_acknowledged_silently(): void
    {
        $this->postJson('/api/telegram/webhook', ['update_id' => 1], $this->secretHeader())
            ->assertOk()
            ->assertJsonMissingPath('method');
    }

    private function update(int $chatId): array
    {
        return [
            'update_id' => 1,
            'message'   => [
                'message_id' => 1,
                'chat'       => ['id' => $chatId, 'type' => 'private'],
                'text'       => '/start',
            ],
        ];
    }

    private function secretHeader(): array
    {
        return ['X-Telegram-Bot-Api-Secret-Token' => app(TelegramService::class)->webhookSecret()];
    }
}
