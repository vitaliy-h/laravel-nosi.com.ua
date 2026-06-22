<?php

namespace Database\Seeders;

use App\Modules\Notification\Enums\SmsEnum;
use App\Modules\Notification\Models\SmsTemplate;
use Illuminate\Database\Seeder;

class SmsTemplateSeeder extends Seeder
{
    public function run(): void
    {
        foreach (SmsEnum::cases() as $case) {
            $data = __("sms_templates.{$case->value}");
            if (!SmsTemplate::where('key', $case)->exists()) {
                SmsTemplate::create([
                    'key' => $case->value,
                    'subject' => $data['subject'],
                    'body' => $data['body'],
                ]);
            }
        }
    }
}
