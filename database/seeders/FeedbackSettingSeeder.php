<?php

namespace Database\Seeders;

use App\Models\FeedbackSetting;
use Illuminate\Database\Seeder;

class FeedbackSettingSeeder extends Seeder
{
    public function run(): void
    {
        FeedbackSetting::set('is_enabled', 'true');
        FeedbackSetting::set('recipient_email', 'maklumbalas@opengovportal.example');
        FeedbackSetting::set('success_message_ms', 'Terima kasih. Maklum balas anda telah diterima dan akan dibalas dalam masa tiga hari bekerja.');
        FeedbackSetting::set('success_message_en', 'Thank you. Your feedback has been received and will be answered within three working days.');
    }
}
