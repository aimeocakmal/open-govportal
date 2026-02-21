<?php

namespace Database\Seeders;

use App\Models\FeedbackSetting;
use Illuminate\Database\Seeder;

class FeedbackSettingSeeder extends Seeder
{
    public function run(): void
    {
        FeedbackSetting::create(['key' => 'is_enabled', 'value' => 'true']);
        FeedbackSetting::create(['key' => 'recipient_email', 'value' => '']);
        FeedbackSetting::create(['key' => 'success_message_ms', 'value' => 'Terima kasih atas maklum balas anda.']);
        FeedbackSetting::create(['key' => 'success_message_en', 'value' => 'Thank you for your feedback.']);
    }
}
