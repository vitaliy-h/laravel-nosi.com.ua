<?php

namespace App\Modules\Core\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class TemplateMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $subjectValue,
        public string $body,
        public array  $attachmentsData = [],
    )
    {
    }

    public function build(): static
    {
        $mail = $this
            ->subject($this->subjectValue)
            ->view('emails.template')
            ->with([
                'subject' => $this->subjectValue,
                'content' => $this->body,
            ]);

        foreach ($this->attachmentsData as $attachment) {

            $path = Storage::path($attachment);

            if (!file_exists($path)) {
                continue;
            }
            $mail->attach($path);
        }

        return $mail;
    }

}
