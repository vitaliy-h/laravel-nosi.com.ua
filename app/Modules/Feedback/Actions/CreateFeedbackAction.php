<?php

namespace App\Modules\Feedback\Actions;

use App\Modules\Feedback\Dtos\CreateFeedbackData;
use App\Modules\Feedback\Models\Feedback;
use App\Modules\Notification\Dtos\SendLetterTemplateData;
use App\Modules\Notification\Enums\LetterEnum;
use App\Modules\Notification\Facades\LetterTemplateFacade;

class CreateFeedbackAction
{
    public function handle(CreateFeedbackData $data): Feedback
    {
        $feedback = Feedback::create([
            'name' => $data->name,
            'email' => $data->email,
            'ip' => $data->ip,
            'message' => $data->message,
        ]);

        LetterTemplateFacade::send(
            new SendLetterTemplateData(
                key: LetterEnum::FEEDBACK,
                replace: [
                    'user_name' => $data->name,
                    'user_email' => $data->email,
                    'message' => $data->message,
                ],
            )
        );

        return $feedback;
    }
}
