<?php

namespace App\Modules\Feedback\Actions;

use App\Modules\Feedback\Dtos\CreateFeedbackData;
use App\Modules\Feedback\Models\Feedback;
use App\Modules\Notification\Dtos\SendLetterTemplateData;
use App\Modules\Notification\Enums\LetterEnum;
use App\Modules\Notification\Services\LetterTemplateService;

class CreateFeedbackAction
{
    public function __construct(
        private readonly LetterTemplateService $letters,
    ) {}

    public function handle(CreateFeedbackData $data): Feedback
    {
        $feedback = Feedback::create([
            'name'    => $data->name,
            'email'   => $data->email,
            'phone'   => $data->phone,
            'ip'      => $data->ip,
            'message' => $data->message,
        ]);

        $this->letters->send(
            new SendLetterTemplateData(
                key: LetterEnum::FEEDBACK,
                replace: [
                    'user_name'  => $data->name,
                    'user_email' => $data->email,
                    'user_phone' => $data->phone ?? '',
                    'message'    => $data->message,
                ],
            )
        );

        return $feedback;
    }
}
