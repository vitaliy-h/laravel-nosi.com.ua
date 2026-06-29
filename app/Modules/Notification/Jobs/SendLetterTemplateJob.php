<?php

namespace App\Modules\Notification\Jobs;

use App\Modules\Notification\Enums\LogLetterStatusEnum;
use App\Modules\Notification\Models\LogLetter;
use App\Modules\Core\Mail\TemplateMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendLetterTemplateJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public LogLetter $logLetter,
    ) {}

    public function handle(): void
    {
        try {
            $mail = (new TemplateMail(
                subjectValue: $this->logLetter->subject,
                body: $this->logLetter->body,
                attachmentsData: $this->logLetter->attachments ?? [],
            ))->from(
                $this->logLetter->sender ?? config('mail.from.address'),
                $this->logLetter->sender_name ?? config('mail.from.name'),
            );

            $finalHtml = $mail->render();

            Mail::to($this->logLetter->receiver)->send($mail);

            $this->logLetter->update([
                'status' => LogLetterStatusEnum::SENT,
                'content' => $finalHtml,
            ]);
        } catch (Throwable $e) {
            $this->logLetter->update([
                'status' => LogLetterStatusEnum::FAILED,
                'error_message' => $e->getMessage(),
            ]);

            report($e);
        }
    }
}
