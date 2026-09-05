<?php

namespace App\Modules\Notification\Services;

use App\Modules\Notification\Dtos\LetterAttachmentData;
use App\Modules\Notification\Dtos\SendLetterTemplateData;
use App\Modules\Notification\Enums\LogLetterStatusEnum;
use App\Modules\Notification\Jobs\SendLetterTemplateJob;
use App\Modules\Notification\Models\LetterTemplate;
use App\Modules\Notification\Models\LogLetter;
use App\Modules\Notification\Traits\DetectsNotificationSource;
use Illuminate\Support\Facades\Storage;

class LetterTemplateService
{
    use DetectsNotificationSource;

    public function send(SendLetterTemplateData $dto): void
    {
        $template = LetterTemplate::query()
            ->where('key', $dto->key)
            ->firstOrFail();

        $subject = $this->replace($template->subject, $dto->replace);
        $body = $this->replace($template->body, $dto->replace);

        $receivers = $dto->to
            ? [$dto->to]
            : $template->receivers_formatted;

        foreach ($receivers as $receiver) {
            $logLetter = LogLetter::query()->create([
                'template_key' => $template->key,
                'receiver'     => $receiver,
                'subject'      => $subject,
                'sender'       => $template->sender,
                'sender_name'  => $template->sender_name,
                'body'         => $body,
                'status'       => LogLetterStatusEnum::QUEUED,
                'source'       => $dto->source ?? $this->detectSource(),
                'attachments'  => $dto->attachments,
            ]);

            $attachments = $this->saveAttachments($logLetter, $dto->attachments);

            $logLetter->update(['attachments' => $attachments]);

            SendLetterTemplateJob::dispatch($logLetter);
        }
    }

    private function replace(string $content, array $replace): string
    {
        foreach ($replace as $key => $value) {
            $pattern = '/{{\s*\$?' . preg_quote($key, '/') . '\s*}}/';
            $content = preg_replace_callback($pattern, fn () => (string) $value, $content);
        }

        return $content;
    }

    private function saveAttachments(LogLetter $logLetter, array $attachments): array
    {
        $paths = [];

        foreach ($attachments as $k => $attachment) {
            if (!$attachment instanceof LetterAttachmentData) {
                continue;
            }

            if (!file_exists($attachment->path)) {
                continue;
            }

            $filename = basename($attachment->path);
            $storagePath = "log_letters/{$logLetter->id}/attachments/{$k}/{$filename}";

            Storage::put($storagePath, file_get_contents($attachment->path));

            $paths[] = $storagePath;
        }

        return $paths;
    }
}
