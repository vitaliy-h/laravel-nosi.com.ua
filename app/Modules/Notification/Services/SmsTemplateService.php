<?php

namespace App\Modules\Notification\Services;

use App\Modules\Notification\Contracts\SmsProviderInterface;
use App\Modules\Notification\Dtos\SendSmsData;
use App\Modules\Notification\Enums\LogSmsStatusEnum;
use App\Modules\Notification\Enums\SmsProviderEnum;
use App\Modules\Notification\Models\LogSms;
use App\Modules\Notification\Models\SmsTemplate;
use App\Modules\Notification\SmsProviders\LogSmsProvider;
use App\Modules\Notification\Traits\DetectsNotificationSource;
use Throwable;

class SmsTemplateService
{
    use DetectsNotificationSource;

    public function send(SendSmsData $dto): void
    {
        $template = SmsTemplate::query()
            ->where('key', $dto->key)
            ->firstOrFail();

        $body = $this->replace($template->body ?? '', $dto->replace);

        $providerEnum = SmsProviderEnum::from(
            config('sms.provider', SmsProviderEnum::LOG->value)
        );

        $logSms = LogSms::create([
            'template_key' => $template->key,
            'receiver'     => $dto->to,
            'subject'      => $template->subject,
            'body'         => $body,
            'status'       => LogSmsStatusEnum::QUEUED,
            'provider'     => $providerEnum,
            'source'       => $dto->source ?? $this->detectSource(),
        ]);

        try {
            $this->resolveProvider()->send($dto->to, $body);

            $logSms->update(['status' => LogSmsStatusEnum::SENT]);
        } catch (Throwable $e) {
            $logSms->update([
                'status'        => LogSmsStatusEnum::FAILED,
                'error_message' => $e->getMessage(),
            ]);

            report($e);
        }
    }

    private function replace(string $content, array $replace): string
    {
        foreach ($replace as $key => $value) {
            $pattern = '/{{\s*\$?' . preg_quote($key, '/') . '\s*}}/';
            $content = preg_replace($pattern, $value, $content);
        }

        return $content;
    }

    protected function resolveProvider(): SmsProviderInterface
    {
        $key = config('sms.provider', SmsProviderEnum::LOG->value);

        return match (SmsProviderEnum::from($key)) {
            SmsProviderEnum::LOG => new LogSmsProvider,
        };
    }
}
