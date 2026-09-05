<?php

namespace Tests\Unit\Notification;

use App\Modules\Notification\Contracts\SmsProviderInterface;
use App\Modules\Notification\Dtos\SendSmsData;
use App\Modules\Notification\Enums\LogSmsStatusEnum;
use App\Modules\Notification\Enums\NotificationSourceEnum;
use App\Modules\Notification\Enums\SmsEnum;
use App\Modules\Notification\Models\SmsTemplate;
use App\Modules\Notification\Services\SmsTemplateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmsTemplateServiceTest extends TestCase
{
    use RefreshDatabase;

    private SmsTemplateService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new SmsTemplateService;

        $this->seedSmsTemplates();
    }

    public function test_send_creates_log_sms_with_sent_status_on_success(): void
    {
        $dto = new SendSmsData(
            key: SmsEnum::VERIFICATION,
            to: '+380991234567',
            replace: ['code' => '123456'],
        );

        $this->service->send($dto);

        $this->assertDatabaseHas('log_sms', [
            'template_key' => SmsEnum::VERIFICATION->value,
            'receiver'     => '+380991234567',
            'status'       => LogSmsStatusEnum::SENT->value,
        ]);
    }

    public function test_send_replaces_placeholders_in_body(): void
    {
        SmsTemplate::query()->where('key', SmsEnum::VERIFICATION)->update([
            'body' => 'Your code is {{ $code }}. Expires in {{ $minutes }} min.',
        ]);

        $dto = new SendSmsData(
            key: SmsEnum::VERIFICATION,
            to: '+380991234567',
            replace: ['code' => '999888', 'minutes' => '5'],
        );

        $this->service->send($dto);

        $this->assertDatabaseHas('log_sms', [
            'receiver' => '+380991234567',
            'body'     => 'Your code is 999888. Expires in 5 min.',
        ]);
    }

    public function test_send_sets_explicit_source_when_provided(): void
    {
        $dto = new SendSmsData(
            key: SmsEnum::VERIFICATION,
            to: '+380991234567',
            replace: ['code' => '000111'],
            source: NotificationSourceEnum::ADMIN,
        );

        $this->service->send($dto);

        $this->assertDatabaseHas('log_sms', [
            'receiver' => '+380991234567',
            'source'   => NotificationSourceEnum::ADMIN->value,
        ]);
    }

    public function test_send_stores_subject_from_template(): void
    {
        SmsTemplate::query()->where('key', SmsEnum::VERIFICATION)->update([
            'subject' => 'Verification',
        ]);

        $dto = new SendSmsData(
            key: SmsEnum::VERIFICATION,
            to: '+380991234567',
            replace: ['code' => '123456'],
        );

        $this->service->send($dto);

        $this->assertDatabaseHas('log_sms', [
            'receiver' => '+380991234567',
            'subject'  => 'Verification',
        ]);
    }

    public function test_send_marks_log_failed_when_provider_throws(): void
    {
        // Override provider to always throw
        $service = new class extends SmsTemplateService
        {
            protected function resolveProvider(): SmsProviderInterface
            {
                return new class implements SmsProviderInterface
                {
                    public function send(string $to, string $body): void
                    {
                        throw new \RuntimeException('Provider unavailable');
                    }
                };
            }
        };

        $dto = new SendSmsData(
            key: SmsEnum::VERIFICATION,
            to: '+380991234567',
            replace: ['code' => '123456'],
        );

        $service->send($dto);

        $this->assertDatabaseHas('log_sms', [
            'receiver'      => '+380991234567',
            'status'        => LogSmsStatusEnum::FAILED->value,
            'error_message' => 'Provider unavailable',
        ]);
    }
}
