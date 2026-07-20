<?php

namespace Tests\Unit\Notification;

use App\Modules\Notification\Dtos\SendLetterTemplateData;
use App\Modules\Notification\Enums\LetterEnum;
use App\Modules\Notification\Enums\LogLetterStatusEnum;
use App\Modules\Notification\Enums\NotificationSourceEnum;
use App\Modules\Notification\Models\LetterTemplate;
use App\Modules\Notification\Services\LetterTemplateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class LetterTemplateServiceTest extends TestCase
{
    use RefreshDatabase;

    private LetterTemplateService $service;

    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();

        $this->service = new LetterTemplateService;

        $this->seedLetterTemplates();
    }

    public function test_send_creates_log_letter_with_queued_status(): void
    {
        $dto = new SendLetterTemplateData(
            key: LetterEnum::PASSWORD_RESET,
            replace: ['reset_url' => 'https://example.com/reset/token'],
            to: 'user@example.com',
        );

        $this->service->send($dto);

        $this->assertDatabaseHas('log_letters', [
            'template_key' => LetterEnum::PASSWORD_RESET->value,
            'receiver' => 'user@example.com',
            'status' => LogLetterStatusEnum::QUEUED->value,
        ]);
    }

    public function test_send_replaces_placeholders_in_subject_and_body(): void
    {
        LetterTemplate::query()->where('key', LetterEnum::PASSWORD_RESET)->update([
            'subject' => 'Reset for {{ $user_name }}',
            'body' => 'Click {{ $reset_url }} to reset.',
        ]);

        $dto = new SendLetterTemplateData(
            key: LetterEnum::PASSWORD_RESET,
            replace: ['user_name' => 'John', 'reset_url' => 'https://example.com/reset/abc'],
            to: 'john@example.com',
        );

        $this->service->send($dto);

        $this->assertDatabaseHas('log_letters', [
            'receiver' => 'john@example.com',
            'subject' => 'Reset for John',
            'body' => 'Click https://example.com/reset/abc to reset.',
        ]);
    }

    public function test_send_replaces_values_containing_dollar_sign_literally(): void
    {
        LetterTemplate::query()->where('key', LetterEnum::PASSWORD_RESET)->update([
            'subject' => 'Total {{ $total_amount }}',
            'body' => 'Total: {{ $total_amount }}, ref {{ $ref }}.',
        ]);

        $dto = new SendLetterTemplateData(
            key: LetterEnum::PASSWORD_RESET,
            replace: ['total_amount' => '$170.00', 'ref' => '$1 not a backreference'],
            to: 'user@example.com',
        );

        $this->service->send($dto);

        $this->assertDatabaseHas('log_letters', [
            'receiver' => 'user@example.com',
            'subject' => 'Total $170.00',
            'body' => 'Total: $170.00, ref $1 not a backreference.',
        ]);
    }

    public function test_send_dispatches_job_per_receiver(): void
    {
        $dto = new SendLetterTemplateData(
            key: LetterEnum::PASSWORD_RESET,
            replace: ['reset_url' => 'https://example.com/reset/token'],
            to: 'user@example.com',
        );

        $this->service->send($dto);

        Queue::assertCount(1);
    }

    public function test_send_uses_template_receivers_when_to_is_null(): void
    {
        LetterTemplate::query()->where('key', LetterEnum::FEEDBACK)->update([
            'receivers' => 'admin@example.com, manager@example.com',
        ]);

        $dto = new SendLetterTemplateData(
            key: LetterEnum::FEEDBACK,
            replace: ['user_name' => 'Alice', 'user_email' => 'alice@example.com', 'message' => 'Hello'],
        );

        $this->service->send($dto);

        $this->assertDatabaseHas('log_letters', ['receiver' => 'admin@example.com']);
        $this->assertDatabaseHas('log_letters', ['receiver' => 'manager@example.com']);
        Queue::assertCount(2);
    }

    public function test_send_sets_explicit_source_when_provided(): void
    {
        $dto = new SendLetterTemplateData(
            key: LetterEnum::PASSWORD_RESET,
            replace: ['reset_url' => 'https://example.com/reset/token'],
            to: 'user@example.com',
            source: NotificationSourceEnum::ADMIN,
        );

        $this->service->send($dto);

        $this->assertDatabaseHas('log_letters', [
            'receiver' => 'user@example.com',
            'source' => NotificationSourceEnum::ADMIN->value,
        ]);
    }

    public function test_send_stores_sender_from_template(): void
    {
        LetterTemplate::query()->where('key', LetterEnum::PASSWORD_RESET)->update([
            'sender' => 'noreply@example.com',
            'sender_name' => 'MyApp',
        ]);

        $dto = new SendLetterTemplateData(
            key: LetterEnum::PASSWORD_RESET,
            replace: ['reset_url' => 'https://example.com/reset/token'],
            to: 'user@example.com',
        );

        $this->service->send($dto);

        $this->assertDatabaseHas('log_letters', [
            'receiver' => 'user@example.com',
            'sender' => 'noreply@example.com',
            'sender_name' => 'MyApp',
        ]);
    }
}
